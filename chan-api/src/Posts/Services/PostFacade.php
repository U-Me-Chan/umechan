<?php

namespace PK\Posts\Services;

use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use PK\Boards\BoardStorage;
use PK\Events\Services\EventTrigger;
use PK\Posts\Post;
use PK\Posts\PostStorage;

class PostFacade
{
    private const LACUNA_STRING = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';

    public function __construct(
        private PostStorage $post_storage,
        private BoardStorage $board_storage,
        private EventTrigger $event_trigger,
        private PostRestorator $post_restorator
    ) {
    }

    /**
     * @throws RuntimeException
     */
    public function restorePostFromEPDSDump(int $timestamp): void
    {
        $this->post_restorator->extractPostDatasFromEPDSAndSaveToInternalDatabase($timestamp);
    }

    /**
     * @throws OutOfBoundsException
     */
    public function getThread(int $id, array $exclude_tags = [], bool $no_board_list = false): array
    {
        $thread = $this->post_storage->findById($id);

        if (!$no_board_list) {
            $boards = $this->board_storage->find($exclude_tags);
        } else {
            $boards = [];
        }

        return [$thread, $boards];
    }

    /**
     * @throws OutOfBoundsException
     */
    public function getThreadList(
        array $tags,
        int $limit = 20,
        int $offset = 0,
        array $exclude_tags = [],
        bool $no_board_list = false
    ): array {
        list($threads, $count) = $this->post_storage->find($limit, $offset, $tags);

        if (!$no_board_list) {
            $boards = $this->board_storage->find($exclude_tags);
        } else {
            $boards = [];
        }

        return [$threads, $count, $boards];
    }

    /**
     * @throws OutOfBoundsException
     */
    public function createReplyOnThread(int $thread_id, string $message, array $params = []): array
    {
        $thread = $this->post_storage->findById($thread_id);

        $post = Post::draft($thread->board, $thread_id, $message);

        if (isset($params['poster'])) {
            $post->poster = $params['poster'];
        }

        if (isset($params['subject'])) {
            $post->subject = $params['subject'];
        }

        $id = $this->post_storage->save($post);

        if (!$thread->bump_limit_reached && !isset($params['sage'])) {
            $thread->updated_at = time();

            $this->post_storage->save($thread);

            $this->event_trigger->triggerThreadUpdated($thread_id);
        }

        $this->event_trigger->triggerPostCreated($id);
        $this->board_storage->updateCounters($thread->board->id);

        return ['post_id' => $id, 'password' => $post->password->clearPasswordToString()];
    }

    /**
     * @throws OutOfBoundsException
     */
    public function createThread(string $tag, string $message, array $params = []): array
    {
        $board = $this->board_storage->findByTag($tag);

        $post = Post::draft($board, null, $message);

        if (isset($params['poster'])) {
            $post->poster = $params['poster'];
        }

        if (isset($params['subject'])) {
            $post->subject = $params['subject'];
        }

        $id = $this->post_storage->save($post);

        $this->event_trigger->triggerPostCreated($id);
        $this->event_trigger->triggerBoardUpdated($board->id);

        $this->board_storage->updateCounters($board->id);

        return ['post_id' => $id, 'password' => $post->password->clearPasswordToString()];
    }

    public function deletePostByOwnerChan(int $id, string $reason = 'Не указано'): void
    {
        $post = $this->post_storage->findById($id);

        $post->subject = self::LACUNA_STRING;
        $post->poster  = self::LACUNA_STRING;
        $post->message = self::LACUNA_STRING;
        $post->message = <<<EOT
{$post->message}

Данные удалены по причине: {$reason}
EOT;
        $post->is_verify = false;

        $this->post_storage->save($post);
        $this->event_trigger->triggerPostDeleted($id);
    }

    public function deletePostByAuthor(int $id, string $password): void
    {
        $post = $this->post_storage->findById($id);

        if (!$post->password->isEqualTo($password)) {
            throw new \RuntimeException('Неверный пароль поста');
        }

        $post->subject = self::LACUNA_STRING;
        $post->poster  = self::LACUNA_STRING;
        $message       = self::LACUNA_STRING;
        $message       = <<<EOT
{$message}

Данные удалены пользователем
EOT;
        $post->message = $message;
        $post->is_verify = false;

        $this->post_storage->save($post);
        $this->event_trigger->triggerPostDeleted($post->id);
    }

    public function setStickyFlagStateToThread(int $id, bool $is_sticky = false): void
    {
        $post = $this->post_storage->findById($id);

        if (!$post->is_thread) {
            throw new InvalidArgumentException('Не является нитью');
        }

        $post->is_sticky = $is_sticky;

        $this->post_storage->save($post);
        $this->event_trigger->triggerThreadUpdated($id);
    }
}
