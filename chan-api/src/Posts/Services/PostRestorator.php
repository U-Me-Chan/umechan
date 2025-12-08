<?php

namespace PK\Posts\Services;

use Medoo\Medoo;
use OutOfBoundsException;
use PDOException;
use PK\Boards\BoardStorage;
use PK\Posts\Post;
use PK\Posts\Post\PasswordHash;
use PK\Posts\PostStorage;
use RuntimeException;

class PostRestorator
{
    private Medoo $db;

    public function __construct(
        private string $path_to_sqlite_epds_dump_file,
        private BoardStorage $board_storage,
        private PostStorage $post_storage
    ) {
    }

    public function extractPostDatasFromEPDSAndSaveToInternalDatabase(int $from_timestamp): void
    {
        try {
            $this->db = new Medoo([
                'database_type' => 'sqlite',
                'database_file' => $this->path_to_sqlite_epds_dump_file
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Не могу открыть дамп EPDS: ' .  $e->getMessage());
        }

        $post_datas = $this->db->select(
            'Post',
            [
                'id',
                'poster',
                'posterVerified',
                'subject',
                'message',
                'timestamp',
                'updatedAt',
                'boardId',
                'parentId'
            ],
            [
                'timestamp[>=]' => $from_timestamp
            ]
        );

        if (empty($post_datas)) {
            return;
        }

        foreach ($post_datas as $post_data) {
            try {
                $board = $this->board_storage->findById($post_data['boardId']);
            } catch (OutOfBoundsException) {
                throw new RuntimeException('Не найдена доска с таким идентификатором: ' . $post_data['boardId']);
            }


            $post = Post::fromArray([
                'id'         => $post_data['id'],
                'poster'     => $post_data['poster'],
                'is_verify'  => $post_data['posterVerified'] == 1 ? 'yes' : 'no',
                'subject'    => $post_data['subject'],
                'message'    => $post_data['message'],
                'timestamp'  => $post_data['timestamp'],
                'updated_at' => $post_data['updatedAt'],
                'board_data' => $board->toArray(),
                'parent_id'  => $post_data['parentId'],
                'estimate'   => 0,
                'password'   => PasswordHash::generate()->toString(),
                'is_sticky'  => 'no'
            ]);

            try {
                $this->post_storage->findById($post->id->value);
            } catch (OutOfBoundsException) {
                $this->post_storage->saveAsIs($post);
            }
        }
    }
}
