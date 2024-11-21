<?php

namespace PK\Domain;

use PK\Base\Timestamp;
use PK\Boards\Board\Board;
use PK\Domain\PostPoster;
use PK\Utils\MediaParsers\ImageLinkParser;
use PK\Utils\MediaParsers\YoutubeLinkParser;

class Post implements \JsonSerializable
{
    /**
     * Создаёт новую нить
     *
     * @param PostPoster $poster  Автор
     * @param string     $subject Тема
     * @param string     $message Сообщение
     * @param Board      $board   Доска
     *
     * @return self
     */
    public static function createThread(
        PostPoster $poster,
        string $subject,
        string $message,
        Board $board
    ): self {
        return new self(
            0,
            $poster,
            $subject,
            $message,
            Timestamp::createDraft(),
            $board,
            null,
            Timestamp::createDraft(),
            PostPassword::createDraft()
        );
    }

    /**
     * Создаёт ответ на нить
     *
     * @param PostPoster $poster  Автор
     * @param string     $message Сообщение
     * @param Post       $parent  Нить
     * @param string     $subject Тема
     *
     * @return self
     */
    public static function createReplyInThread(
        PostPoster $poster,
        string $message,
        Post $parent,
        string $subject = ''
    ): self
    {
        return new self(
            0,
            $poster,
            $subject,
            $message,
            Timestamp::createDraft(),
            $parent->getBoard(),
            $parent->getId(),
            Timestamp::createDraft(),
            PostPassword::createDraft()
        );
    }

    public static function createFromArray(array $state): self
    {
        return new self(
            $state['id'],
            PostPoster::createFromString($state['poster']),
            $state['subject'],
            $state['message'],
            Timestamp::createFromInt($state['timestamp']),
            Board::fromArray($state['board_data']),
            $state['parent_id'],
            Timestamp::createFromInt($state['updated_at']),
            PostPassword::createFromString($state['password']),
            $state['is_verify']
        );
    }

    public function erase(): void
    {
        $this->subject = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
        $this->poster  = PostPoster::createFromString('⬛⬛⬛⬛⬛⬛⬛⬛⬛');
        $this->message = '⬛⬛⬛⬛⬛⬛⬛⬛⬛';
    }

    public function bump(): void
    {
        if ($this->parent_id !== null) {
            throw new \Exception();
        }

        $this->updated_at = Timestamp::createDraft();
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password->toString();
    }

    public function jsonSerialize(): array
    {
        list($youtubes, $message) = YoutubeLinkParser::execute($this->message);
        list($images, $message) = ImageLinkParser::execute($message);

        return [
            'id'                => $this->id,
            'poster'            => $this->poster->getPoster(),
            'subject'           => $this->subject,
            'message'           => $this->message,
            'datetime'          => $this->timestamp,
            'board_id'          => $this->board->id,
            'board'             => $this->board,
            'parent_id'         => $this->parent_id,
            'updated_at'        => $this->updated_at,
            'is_verify'         => $this->is_verify,
            'truncated_message' => $message,
            'media'      =>  [
                'youtubes' => $youtubes,
                'images'   => $images
            ],
        ];
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'poster'     => $this->poster->getPoster(),
            'subject'    => $this->subject,
            'message'    => $this->message,
            'timestamp'  => $this->timestamp->toInt(),
            'board_id'   => $this->board->id,
            'parent_id'  => $this->parent_id,
            'updated_at' => $this->updated_at->toInt(),
            'password'   => $this->password->toString(),
            'iv_verify'  => $this->is_verify
        ];
    }

    private function __construct(
        private int $id,
        private PostPoster $poster,
        private string $subject,
        private string $message,
        private Timestamp $timestamp,
        private Board $board,
        private ?int $parent_id,
        private Timestamp $updated_at,
        private PostPassword $password,
        private bool $is_verify = false,
        private array $replies = [],
        private int $replies_count = 0,
    ) {
    }
}
