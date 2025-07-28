<?php

namespace PK\Posts;

use InvalidArgumentException;
use Medoo\Medoo;
use OutOfBoundsException;
use PDOStatement;
use PK\Posts\Post;
use PK\Boards\BoardStorage;
use PK\Passports\PassportStorage;
use PK\Posts\Post\PosterKeyHash;
use PK\Posts\Post\VerifyFlag;

class PostStorage
{
    public function __construct(
        private Medoo $db,
        private BoardStorage $board_storage,
        private PassportStorage $passport_storage
    ) {
    }

    public function find(int $limit = 20, int $offset = 0, array $tags = []): array
    {
        $conditions = [
            'posts.parent_id' => null,
            'boards.tag' => $tags
        ];

        $ordering = [
            'ORDER' => [
                'posts.updated_at' => 'DESC'
            ],
        ];

        if (sizeof($tags) == 1) {
            $ordering['ORDER']['posts.is_sticky'] = 'ASC'; // порядок сортировки зависит от порядка указания значений для колонки с типом enum в MySQL
        }

        if ($limit > 100) {
            throw new InvalidArgumentException('Не допускается такой большой запрос');
        }

        $limit = ['LIMIT' => [$offset, $limit]];

        $count = $this->db->count(
            'posts',
            [
                '[>]boards' => ['board_id' => 'id']
            ],
            [
                'posts.id'
            ],
            $conditions
        );

        if ($count == 0) {
            return [[], 0];
        }

        $thread_datas = $this->db->select(
            'posts',
            [
                '[>]boards'   => ['board_id' => 'id'],
                '[>]posts(r)' => ['posts.id' => 'parent_id']
            ],
            [
                'posts.id',
                'posts.poster',
                'posts.subject',
                'posts.message',
                'posts.timestamp',
                'posts.parent_id',
                'posts.updated_at',
                'posts.estimate',
                'posts.is_verify',
                'posts.is_sticky',
                'posts.password',
                'posts.board_id',
                'boards.tag',
                'board_data' => [
                    'boards.id(board_id)',
                    'boards.tag',
                    'boards.name',
                    'boards.threads_count',
                    'boards.new_posts_count'
                ],
                'replies_count' => Medoo::raw('COUNT(r.id)')
            ],
            array_merge(
                $conditions,
                $limit,
                [
                    'GROUP' => [
                        'posts.id',
                    ]
                ], $ordering
            )
        );

        $threads = [];

        foreach ($thread_datas as $thread_data) {
            $thread_data['replies'] = array_map(
                fn(array $post_data) => Post::fromArray($post_data),
                array_reverse($this->db->select(
                    'posts',
                    [
                        '[>]boards' => ['board_id' => 'id']
                    ],
                    [
                        'posts.id',
                        'posts.poster',
                        'posts.subject',
                        'posts.message',
                        'posts.timestamp',
                        'posts.parent_id',
                        'posts.updated_at',
                        'posts.estimate',
                        'posts.is_verify',
                        'posts.is_sticky',
                        'posts.password',
                        'board_data' => [
                            'boards.id(board_id)',
                            'boards.tag',
                            'boards.name',
                            'boards.threads_count',
                            'boards.new_posts_count'
                        ]
                    ],
                    [
                        'posts.parent_id' => $thread_data['id'],
                        'LIMIT' => 3,
                        'ORDER' => ['id' => 'DESC']
                    ]
                ))
            );

            $threads[] = Post::fromArray($thread_data);
        }

        return [$threads, $count];
    }

    public function findById(int $id): Post
    {
        $thread_and_replies_datas = $this->db->select(
            'posts',
            [
                '[>]boards' => ['board_id' => 'id'],
            ],
            [
                'posts.id',
                'posts.poster',
                'posts.subject',
                'posts.message',
                'posts.timestamp',
                'posts.parent_id',
                'posts.updated_at',
                'posts.estimate',
                'posts.is_verify',
                'posts.is_sticky',
                'posts.password',
                'board_data' => [
                    'boards.id(board_id)',
                    'boards.tag',
                    'boards.name',
                    'boards.threads_count',
                    'boards.new_posts_count'
                ]
            ],
            [
                'OR' => [
                    'posts.id' => $id,
                    'posts.parent_id' => $id
                ]
            ]
        );

        if (empty($thread_and_replies_datas)) {
            throw new OutOfBoundsException('Нет такой нити');
        }

        $thread_data = array_shift($thread_and_replies_datas);

        $thread_data['replies']       = array_map(fn(array $post_data) => Post::fromArray($post_data), $thread_and_replies_datas);
        $thread_data['replies_count'] = $this->db->count('posts', ['parent_id' => $thread_data['id']]);

        return Post::fromArray($thread_data);
    }

    public function save(Post $post): int
    {
        $post_data = $post->toArray();

        unset($post_data['id']);

        if ($post->is_draft) {
            try {
                $passport = $this->passport_storage->findOne(['hash' => PosterKeyHash::fromString($post_data['poster'])->toString()]);

                $post_data['poster'] = $passport->name->toString();
                $post_data['is_verify'] = VerifyFlag::yes->value;
            } catch (OutOfBoundsException) {
                $post_data['is_verify'] = VerifyFlag::no->value;
            }

            $this->db->insert('posts', $post_data);

            return $this->db->id();
        }

        $this->db->update('posts', $post_data, ['id' => $post->id]);

        return $post->id;
    }

    public function delete(int $id): bool
    {
        /** @var PDOStatement */
        $pdo = $this->db->delete('posts', ['id' => $id]);

        if ($pdo->rowCount() == 1) {
            return true;
        }

        throw new \OutOfBoundsException();
    }
}
