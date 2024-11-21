<?php

namespace PK\Application\CommandHandlers;

use PK\Application\ICommand;
use PK\Application\CommandExecuteStatus;
use PK\Application\Commands\CreateThread;
use PK\Application\Commands\CreateThreadReply;
use PK\Application\Commands\DeletePost;
use PK\Application\Commands\DeleteThread;
use PK\Application\CommandStatus;
use PK\Application\ICommandHandler;
use PK\Domain\Post;
use PK\Domain\PostPoster;
use PK\Exceptions\BoardNotFound;
use PK\Exceptions\PassportNotFound;
use PK\Exceptions\PostNotFound;
use PK\Infrastructure\IBoardRepository;
use PK\Infrastructure\IPassportRepository;
use PK\Infrastructure\IPostRepository;

class PostCommandHandler implements ICommandHandler
{
    public function __construct(
        private IPostRepository $post_repo,
        private IBoardRepository $board_repo,
        private IPassportRepository $passport_repo
    ) {
    }

    public function execute(ICommand $command): CommandExecuteStatus
    {
        if ($command instanceof CreateThread) {
            return $this->createThreadHandler($command);
        } else if ($command instanceof CreateThreadReply) {
            return $this->createReplyHandler($command);
        } else if ($command instanceof DeletePost) {
            return $this->deletePostHandler($command);
        } else if ($command instanceof DeleteThread) {
            return $this->deleteThreadHandler($command);
        } else {
            return new CommandExecuteStatus(
                CommandStatus::FAILED,
                $command->toArray(),
                [],
                'Неизвестная команда'
            );
        }
    }

    private function deleteThreadHandler(DeleteThread $command): CommandExecuteStatus
    {
        return new CommandExecuteStatus(CommandStatus::FAILED, $command->toArray());
    }

    private function deletePostHandler(DeletePost $command): CommandExecuteStatus
    {
        try {
            $post = $this->post_repo->findOne(['id' => $command->post_id]);
        } catch (PostNotFound) {
            return new CommandExecuteStatus(CommandStatus::FAILED, $command->toArray(), [], 'Нет такого поста');
        }

        if (!hash_equals($post->getPassword(), $command->password)) {
            return new CommandExecuteStatus(CommandStatus::FAILED, $command->toArray(), [], 'Пароль неверен');
        }

        $this->post_repo->save($post->erase());

        return new CommandExecuteStatus(CommandStatus::SUCCESS, $command->toArray());
    }

    private function createReplyHandler(CreateThreadReply $command): CommandExecuteStatus
    {
        try {
            $thread = $this->post_repo->findOne(['id' => $command->thread_id]);
        } catch (PostNotFound) {
            return new CommandExecuteStatus(CommandStatus::FAILED, $command->toArray(), [], 'Нет такой нити');
        }

        try {
            $passport = $this->passport_repo->findOne(['password' => $command->poster]);

            $poster = PostPoster::createFromString($passport->name, true);
        } catch (PassportNotFound) {
            $poster = PostPoster::createFromString($command->poster);
        }

        $reply = Post::createReplyInThread($poster, $command->messsage, $thread, $command->subject);

        $this->post_repo->save($reply);

        return new CommandExecuteStatus(
            CommandStatus::SUCCESS,
            $command->toArray(),
            [
                'post' => $reply
            ]
        );
    }

    private function createThreadHandler(CreateThread $command): CommandExecuteStatus
    {
        try {
            $board = $this->board_repo->findOne(['tag' => $command->tag]);
        } catch (BoardNotFound) {
            return new CommandExecuteStatus(CommandStatus::FAILED, $command->toArray(), [], "Нет доски с таким тегом: {$command->tag}");
        }

        try {
            $passport = $this->passport_repo->findOne(['password' => $command->poster]);

            $poster = PostPoster::createFromString($passport->name, true);
        } catch (PassportNotFound) {
            $poster = PostPoster::createFromString($command->poster);
        }

        $thread = Post::createThread(
            $poster,
            $command->subject,
            $command->message,
            $board
        );

        $this->post_repo->save($thread);

        return new CommandExecuteStatus(
            CommandStatus::SUCCESS,
            $command->toArray(),
            [
                'thread' => $thread
            ]
        );
    }
}
