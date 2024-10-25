<?php

namespace PK\Posts\Repositories;

use Medoo\Medoo;
use PK\Posts\Post\Post;
use PK\Boards\Board\Board;
use PK\Posts\IPostRepository;

class MedooPostByBoardRepository implements IPostRepository
{
    public function __construct(
        private Medoo $db
    ) {
    }

    /**
     * Выполняет поиск постов
     *
     * @param array $filters                  Список фильтров
     * @param ?int  $filters[$parent_id]      Идентификатор родительского поста
     * @param array $filters[$board_ids]      Список идентификаторов досок
     * @param array $filters[$board_tags]     Список тегов досок
     * @param int   $filters[$timestamp_from] unixtime-метка, с которой начинать поиск
     *
     * @return array
     */
    public function findMany(
        array $filters = [
            'parent_id' => null
        ],
        array $ordering = [
            'posts.updated_at' => 'DESC'
        ],
        bool $is_recirsive = false
    ): array
    {
        $conditions = [];

        $limiting['LIMIT'] = [
            isset($filters['offset']) ? $filters['offset'] : 0,
            isset($filters['limit']) ? $filters['limit'] : 20
        ];

        if (array_key_exists('parent_id', $filters)) {
            $conditions['posts.parent_id'] = $filters['parent_id'];
        }

        if (isset($filters['board_ids'])) {
            $conditions['posts.board_id'] = $filters['board_ids'];
        }

        if (isset($filters['board_tags'])) {
            $conditions['boards.tag'] = $filters['board_tags'];
        }

        if (isset($filters['timestamp_from'])) {
            $conditions['posts.timestamp[>=]'] = $filters['timestamp_from'];
        }

        $count = $this->db->count(
            'posts',
            [
                '[>]boards' => ['board_id' => 'id']
            ],
            'posts.id',
            $conditions
        );

        if ($count == 0) {
            return [[], 0];
        }

        $post_datas = $this->db->select(
            'posts',
            [
                '[>]boards' => ['board_id' => 'id']
            ],
            [
                'boards.tag',
                'boards.name',
                'posts.board_id',
                'posts.id',
                'posts.poster',
                'posts.subject',
                'posts.message',
                'posts.is_verify',
                'posts.timestamp',
                'posts.parent_id',
                'posts.updated_at',
                'posts.estimate',
                'posts.password',
                'boards.threads_count',
                'boards.new_posts_count'
            ],
            array_merge($conditions, $limiting, ['ORDER' => $ordering])
        );


        $posts = array_map(function(array $agg_data) use ($is_recirsive) {
            $board = Board::fromArray($agg_data);

            $post = Post::fromArray(array_merge($agg_data, ['board_data' => $board->toArray()]));

            if (!$is_recirsive) {
                list($replies, $replies_count) = $this->findMany(['parent_id' => $agg_data['id'], 'limit' => 3], ['posts.id' => 'DESC'], true);

                if ($replies_count !== 0) {
                    $post->replies_count = $replies_count;

                    $replies = array_reverse($replies);

                    foreach ($replies as $reply) {
                        $post->addReply($reply);
                    }
                }
            }

            return $post;
        }, $post_datas);

        return [$posts, $count];
    }

    public function findOne(array $filters = []): Post
    {
        $conditions = [];

        if ($filters['id']) {
            $conditions['posts.id'] = $filters['id'];
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException();
        }

        $agg_data = $this->db->get(
            'posts',
            [
                '[>]boards' => ['board_id' => 'id']
            ],
            [
                'boards.tag',
                'boards.name',
                'posts.board_id',
                'posts.id',
                'posts.poster',
                'posts.subject',
                'posts.message',
                'posts.is_verify',
                'posts.timestamp',
                'posts.parent_id',
                'posts.updated_at',
                'posts.estimate',
                'posts.password',
                'boards.threads_count',
                'boards.new_posts_count'
            ],
            $conditions
        );

        if (!$agg_data) {
            throw new \OutOfBoundsException();
        }

        $board = Board::fromArray($agg_data);
        $post = Post::fromArray(array_merge($agg_data, ['board_data' => $board->toArray()]));

        list($replies, $count) = $this->findMany(['parent_id' => $post->id, 'limit' => 999], ['posts.id' => 'ASC'], true);

        if ($count == 0) {
            return $post;
        }

        $post->replies_count = $count;

        foreach ($replies as $reply) {
            $post->addReply($reply);
        }

        return $post;
    }

    public function save(Post $post): int
    {
        $id = $post->id;

        $post_data = $post->toArray();
        unset($post_data['id']);

        if ($id == 0) {
            $this->db->insert('posts', $post_data);

            return $this->db->id();
        }

        $this->db->update('posts', $post_data, ['id' => $id]);

        return $id;
    }

    public function delete(Post $post): bool
    {
        return true;
    }
}
