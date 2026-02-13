<?php

namespace PK\Posts\Services;

use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use PK\Services\HookService;
use PK\Utils\ApplicationHook;
use PK\Boards\Services\BoardService;
use PK\Events\ChanEventBuilder;
use PK\Events\FilestoreEventBuilder;
use PK\Events\MessageBroker;
use PK\Passports\Services\PassportService;
use PK\Posts\Events\ChanEvents\BumpThread;
use PK\Posts\Events\ChanEvents\BumpThreadPayload;
use PK\Posts\Events\ChanEvents\CreateReplyOnThread;
use PK\Posts\Events\ChanEvents\CreateReplyOnThreadPayload;
use PK\Posts\Events\ChanEvents\CreateThread;
use PK\Posts\Events\ChanEvents\CreateThreadPayload;
use PK\Posts\Events\ChanEvents\DeletePostByAuthor;
use PK\Posts\Events\ChanEvents\DeletePostByAuthorPayload;
use PK\Posts\Events\ChanEvents\DeletePostByOwnerChan;
use PK\Posts\Events\ChanEvents\DeletePostByOwnerChanPayload;
use PK\Posts\Events\ChanEvents\UpdatePost;
use PK\Posts\Events\ChanEvents\UpdatePostPayload;
use PK\Posts\Events\FilestoreEvents\DeleteFile;
use PK\Posts\Events\FilestoreEvents\DeleteFilePayload;
use PK\Posts\Exceptions\InvalidPostPasswordException;
use PK\Posts\Exceptions\NotIsThreadException;
use PK\Posts\Exceptions\ThreadBlockedException;
use PK\Posts\Exceptions\ThreadNotFoundException;
use PK\Posts\Post;
use PK\Posts\Post\PosterKeyHash;
use PK\Posts\PostStorage;

class PostService
{
    private const LACUNA_STRING = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';

    public function __construct(
        private PostStorage $post_storage,
        private BoardService $board_service,
        private PassportService $passport_service,
        private MessageBroker $message_broker,
        private ChanEventBuilder $chan_event_builder,
        private FilestoreEventBuilder $filestore_event_builder,
        private PostRestorator $post_restorator,
        private HookService $hook_service
    ) {
    }

    /**
     * Восстанавить посты из дампа внешнего сервиса EPDS
     *
     * @param int $timestamp Отметка времени, с которой следует взять посты из дампа
     *
     * @deprecated
     * @throws RuntimeException
     */
    public function restorePostFromEPDSDump(int $timestamp): void
    {
        $this->post_restorator->extractPostDatasFromEPDSAndSaveToInternalDatabase($timestamp);
    }

    /**
     * Получить данные нити
     *
     * @param int   $id            Идентификатор нити
     * @param array $exclude_tags  Исключаемые теги досок из списка
     * @param bool  $no_board_list Вернуть список досок?
     *
     * @throws ThreadNotFoundException
     *
     * @return array
     */
    public function getThread(int $id, array $exclude_tags = [], bool $no_board_list = false): array
    {
        $thread = $this->post_storage->findById($id);

        if (!$no_board_list) {
            $boards = $this->board_service->getBoardList($exclude_tags);
        } else {
            $boards = [];
        }

        return [$thread, $boards];
    }

    /**
     * Получить список нитей с ответами для списка тегов досок
     *
     * @param array $tags          Список тегов досок
     * @param int   $offset        Смещение в списке нитей
     * @param int   $limit         Количество нитей
     * @param array $exclude_tags  Исключаемые теги досок из списка
     * @param bool  $no_board_list Вернуть список досок
     *
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
            $boards = $this->board_service->getBoardList($exclude_tags);
        } else {
            $boards = [];
        }

        return [$threads, $count, $boards];
    }

    /**
     * Создать ответ на нить
     *
     * @param array  $params    Дополнительные поля
     * @pramm string $message   Сообщение
     * @param int    $thread_id Идентификатор нити
     *
     * @throws ThreadNotFoundException
     */
    public function createReplyOnThread(int $thread_id, string $message, array $params = []): array
    {
        $thread = $this->post_storage->findById($thread_id);

        if ($thread->is_blocked) {
            throw new ThreadBlockedException('Ответ на нить запрещён');
        }

        $post = Post::draft($thread->board, $thread_id, $message);

        if (isset($params['poster'])) {
            try {
                $passport = $this->passport_service->findByHash(PosterKeyHash::fromString($params['poster'])->toString());
                $post->poster = $passport->name->toString();
                $post->is_verify = true;
            } catch (OutOfBoundsException) {
                $post->poster = $params['poster'];
            }
        }

        if (isset($params['subject'])) {
            $post->subject = $params['subject'];
        }

        $id = $this->post_storage->save($post);

        if (!$thread->bump_limit_reached && !isset($params['sage'])) {
            $thread->updated_at = time();

            $this->post_storage->save($thread);

            $this->message_broker->publish(
                $this->chan_event_builder->build(
                    BumpThread::class,
                    new BumpThreadPayload($thread)
                )
            );
        }

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                CreateReplyOnThread::class,
                new CreateReplyOnThreadPayload($post)
            )
        );

        $this->hook_service->registerHookHandler(ApplicationHook::after_send, fn() => $this->board_service->updateNewPostsCount($thread->board));

        return ['post_id' => $id, 'password' => $post->password->clearPasswordToString()];
    }

    /**
     * @throws OutOfBoundsException
     */
    public function createThread(string $tag, string $message, array $params = []): array
    {
        $board = $this->board_service->getBoardByTag($tag);

        $post = Post::draft($board, null, $message);

        if (isset($params['poster'])) {
            try {
                $passport = $this->passport_service->findByHash(PosterKeyHash::fromString($params['poster'])->toString());
                $post->poster = $passport->name->toString();
                $post->is_verify = true;
            } catch (OutOfBoundsException) {
                $post->poster = $params['poster'];
            }
        }

        if (isset($params['subject'])) {
            $post->subject = $params['subject'];
        }

        $id = $this->post_storage->save($post);

        $this->hook_service->registerHookHandler(ApplicationHook::after_send, fn() => $this->board_service->updateNewPostsCount($post->board));
        $this->hook_service->registerHookHandler(ApplicationHook::after_send, fn() => $this->board_service->updateThreadsCount($post->board));

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                CreateThread::class,
                new CreateThreadPayload(
                    $post
                )
            )
        );

        return ['post_id' => $id, 'password' => $post->password->clearPasswordToString()];
    }

    public function deletePostByOwnerChan(int $id, string $reason = 'Не указано'): void
    {
        $post = $this->post_storage->findById($id);

        foreach ($post->getMedia() as $media_data) {
            $this->message_broker->publish(
                $this->filestore_event_builder->build(
                    DeleteFile::class,
                    new DeleteFilePayload($media_data['link'])
                )
            );
        }

        $post->subject = self::LACUNA_STRING;
        $post->poster  = self::LACUNA_STRING;
        $post->message = self::LACUNA_STRING;
        $post->message = <<<EOT
{$post->message}

Данные удалены по причине: {$reason}
EOT;
        $post->is_verify = false;

        $this->post_storage->save($post);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                DeletePostByOwnerChan::class,
                new DeletePostByOwnerChanPayload($post->id->value, $reason)
            )
        );
    }

    public function updatePostByAuthor(int $id, array $params): void
    {
        if (!isset($params['password'])) {
            throw new InvalidPostPasswordException('Не задан пароль поста');
        }

        unset($params['password']);

        $post = $this->post_storage->findById($id);

        if (!$post->password->isEqualTo($params['password'])) { // @phpstan-ignore offsetAccess.notFound
            throw new InvalidPostPasswordException('Неверный пароль поста');
        }

        $props = Post::getAllowedMutationPropsList();

        $changed_fields = [];

        foreach ($params as $param => $value) {
            if (!in_array($param, $props)) {
                throw new InvalidArgumentException();
            }

            if ($param == 'poster') {
                try {
                    $passport = $this->passport_service->findByHash(PosterKeyHash::fromString($value)->toString());
                    $post->poster = $passport->name->toString();
                    $post->is_verify = true;
                } catch (OutOfBoundsException) {
                    $post->poster = $value;
                }

                $changed_fields['poster'] = $post->poster;

                continue;
            }

            $post->$param = $value;

            $changed_fields[$param] = $value;
        }

        $this->post_storage->save($post);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                UpdatePost::class,
                new UpdatePostPayload($changed_fields)
            )
        );
    }

    public function deletePostByAuthor(int $id, string $password): void
    {
        $post = $this->post_storage->findById($id);

        if (!$post->password->isEqualTo($password)) {
            throw new InvalidPostPasswordException('Неверный пароль поста');
        }

        foreach ($post->getMedia() as $media_data) {
            $this->message_broker->publish(
                $this->filestore_event_builder->build(
                    DeleteFile::class,
                    new DeleteFilePayload($media_data['link'])
                )
            );
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

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                DeletePostByAuthor::class,
                new DeletePostByAuthorPayload($post->id->value)
            )
        );
    }

    public function setStickyFlagStateToThread(int $id, bool $is_sticky = false): void
    {
        $post = $this->post_storage->findById($id);

        if (!$post->is_thread) {
            throw new NotIsThreadException('Не является нитью');
        }

        $post->is_sticky = $is_sticky;

        $this->post_storage->save($post);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                UpdatePost::class,
                new UpdatePostPayload([
                    'is_sticky' => $is_sticky
                ])
            )
        );
    }

    public function setBlockedFlagStateToThread(int $id, bool $is_blocked = false): void
    {
        $thread = $this->post_storage->findById($id);

        if (!$thread->is_thread) {
            throw new NotIsThreadException();
        }

        $thread->is_blocked = $is_blocked;

        $this->post_storage->save($thread);

        $this->message_broker->publish(
            $this->chan_event_builder->build(
                UpdatePost::class,
                new UpdatePostPayload([
                    'is_blocked' => $is_blocked
                ])
            )
        );
    }

    public function getThreadFiles(int $thread_id): array
    {
        /** @var Post */
        list($thread) = $this->getThread($thread_id, no_board_list: true);

        $result = $thread->getMedia();

        /** @var Post $post */
        foreach ($thread->replies as $post) {
            $result = array_merge($result, array_values($post->getMedia()));
        }

        return $result;
    }
}
