<?php

namespace PK\Posts;

use InvalidArgumentException;
use PDOStatement;
use Medoo\Medoo;
use PK\Posts\Post;
use PK\Posts\Post\Id;
use PK\Posts\Exceptions\ThreadNotFoundException;

class PostStorage
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * @param string[] $tags
     *
     * @return array{list<Post>, int}
     */
    public function find(int $limit = 20, int $offset = 0, array $tags = []): array
    {
        $conditions = [
            'posts.parent_id' => null,
            'boards.tag'      => $tags
        ];

        $ordering = [];

        if (sizeof($tags) == 1) {
            $ordering['ORDER']['is_sticky'] = 'ASC'; // порядок сортировки зависит от порядка указания значений для колонки с типом enum в MySQL
        }

        $ordering['ORDER']['posts.updated_at'] = 'DESC'; // важно сохранять порядок передачи колонок для сортировки

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
            ],
            [
                'posts.id',
                'posts.poster',
                'posts.subject',
                'posts.message',
                'posts.timestamp',
                'posts.parent_id',
                'posts.updated_at',
                'posts.is_verify',
                'posts.is_sticky',
                'posts.is_blocked',
                'posts.password',
                'posts.board_id',
                'posts.replies_count',
                'boards.tag',
                'board_data' => [
                    'boards.id(board_id)',
                    'boards.tag',
                    'boards.name',
                    'boards.threads_count',
                    'boards.new_posts_count',
                    'boards.is_public'
                ],
            ],
            array_merge(
                $conditions,
                $limit,
                $ordering
            )
        );

        $thread_ids = array_column($thread_datas, 'id');

        $replies_datas = $this->db->query("
    WITH RankedReplies AS (
        SELECT
            posts.id,
            posts.poster,
            posts.subject,
            posts.message,
            posts.timestamp,
            posts.parent_id,
            posts.updated_at,
            posts.is_verify,
            posts.is_sticky,
            posts.is_blocked,
            posts.password,
            posts.replies_count,
            boards.id AS board_id,
            boards.tag AS board_tag,
            boards.name AS board_name,
            boards.threads_count AS board_threads_count,
            boards.new_posts_count AS board_new_posts_count,
            boards.is_public AS board_is_public,
            ROW_NUMBER() OVER (PARTITION BY posts.parent_id ORDER BY posts.id DESC) as rn
        FROM posts
        LEFT JOIN boards ON posts.board_id = boards.id
        WHERE posts.parent_id IN (" . implode(',', $thread_ids) . ")
    )
    SELECT * FROM RankedReplies WHERE rn <= 3
    ")->fetchAll($this->db->pdo::FETCH_ASSOC);

        $replies_by_thread = [];

        foreach ($replies_datas as $replies_data) {
            $parent_id = $replies_data['parent_id'];

            if (!isset($replies_by_thread[$parent_id])) {
                $replies_by_thread[$parent_id] = [];
            }

            $replies_data['board_data'] = [
                'id'              => $replies_data['board_id'],
                'tag'             => $replies_data['board_tag'],
                'name'            => $replies_data['board_name'],
                'threads_count'   => $replies_data['board_threads_count'],
                'new_posts_count' => $replies_data['board_new_posts_count'],
                'is_public'       => $replies_data['board_is_public']
            ];

            array_unshift($replies_by_thread[$parent_id], Post::fromArray($replies_data));
        }

        $threads = [];

        foreach ($thread_datas as $thread_data) {
            $thread = Post::fromArray($thread_data);
            $thread->replies = $replies_by_thread[$thread_data['id']] ?? [];

            $threads[] = $thread;
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
                'posts.is_verify',
                'posts.is_sticky',
                'posts.is_blocked',
                'posts.password',
                'posts.replies_count',
                'board_data' => [
                    'boards.id(board_id)',
                    'boards.tag',
                    'boards.name',
                    'boards.threads_count',
                    'boards.new_posts_count',
                    'boards.is_public'
                ]
            ],
            [
                'OR' => [
                    'posts.id' => $id,
                    'posts.parent_id' => $id
                ],
                'ORDER' => [
                    'posts.parent_id' => 'ASC', // костыль для тредов, куда перенесли старые посты
                    'posts.timestamp' => 'ASC'
                ]
            ]
        );

        if (empty($thread_and_replies_datas)) {
            throw new ThreadNotFoundException('Нет такой нити');
        }

        $thread_data = array_shift($thread_and_replies_datas);

        $thread_data['replies']       = array_map(fn(array $post_data) => Post::fromArray($post_data), $thread_and_replies_datas);
        $thread_data['replies_count'] = $this->db->count('posts', ['parent_id' => $thread_data['id']]);

        return Post::fromArray($thread_data);
    }

    /**
     * @deprecated
     */
    public function saveAsIs(Post $post): void
    {
        $this->db->insert('posts', $post->toArray());
    }

    public function save(Post $post): Id
    {
        $post_data = $post->toArray();

        if ($post->is_draft) {
            $this->db->insert('posts', $post_data);
        } else {
            unset($post_data['id']);

            $this->db->update('posts', $post_data, ['id' => $post->id->value]);
        }

        return $post->id;
    }

    public function delete(int $id): bool
    {
        /** @var PDOStatement */
        $pdo = $this->db->delete('posts', [
            'OR' => [
                'id'        => $id,
                'parent_id' => $id
            ]
        ]);

        if ($pdo->rowCount() > 0) {
            return true;
        }

        throw new ThreadNotFoundException();
    }

    public function getRepliesCountById(Id $id): int
    {
        return $this->db->count('posts', ['parent_id' => $id->value]) ?? 0;
    }
}
