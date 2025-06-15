<?php

namespace PK\Posts;

use PK\Boards\Board\Board;
use PK\Posts\Post\ImageParser;
use PK\Posts\Post\PasswordHash;
use PK\Posts\Post\StickyFlag;
use PK\Posts\Post\VerifyFlag;
use PK\Posts\Post\VideoParser;
use PK\Posts\Post\YoutubeParser;

class Post implements \JsonSerializable
{
    public bool $bump_limit_reached {
        get => $this->parent_id == null && $this->replies_count > 500 ? true : false;
    }

    public bool $is_draft {
        get => $this->id == 0 ? true : false;
    }

    public static function draft(
        Board $board,
        ?int $parent_id,
        string $message,
        string $poster = 'Anonymous',
        string $subject = ''
    ): self {
        return new self(
            0,
            $poster,
            $subject,
            $message,
            time(),
            $board,
            $parent_id,
            time(),
            0,
            PasswordHash::generate()
        );
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['poster'],
            $state['subject'],
            $state['message'],
            $state['timestamp'],
            Board::fromArray($state['board_data']),
            $state['parent_id'],
            $state['updated_at'],
            $state['estimate'],
            PasswordHash::fromString($state['password']),
            isset($state['replies']) && !empty($state['replies']) ? $state['replies'] : [],
            isset($state['replies_count']) ? $state['replies_count'] : 0,
            VerifyFlag::from($state['is_verify']) == VerifyFlag::yes ? true : false,
            StickyFlag::from($state['is_sticky']) == StickyFlag::yes ? true: false
        );
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);

        list($media, $truncated_message) = $this->getMediaAndTruncatedMessage();

        $data['board_id']          = $data['board']->id;
        $data['media']             = $media;
        $data['truncated_message'] = $truncated_message;
        $data['datetime']          = date('Y-m-d G:i:s', $data['timestamp'] + 60 * (60 * 4));

        unset(
            $data['password'],
            $data['is_draft']
        );

        return $data;
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);

        $data['board_id']  = $this->board->id;
        $data['is_verify'] = $this->is_verify == true ? 'yes' : 'no';
        $data['password']  = $this->password->toString();

        unset(
            $data['board'],
            $data['replies'],
            $data['replies_count'],
            $data['is_sticky'],
            $data['bump_limit_reached'],
            $data['is_draft']
        );

        return $data;
    }

    private function __construct(
        public int $id,
        public string $poster,
        public string $subject,
        public string $message,
        public int $timestamp,
        public Board $board,
        public ?int $parent_id,
        public int $updated_at,
        public int $estimate,
        public PasswordHash $password,
        public array $replies = [],
        public int $replies_count = 0,
        public bool $is_verify = false,
        public bool $is_sticky = false
    ) {
    }

    public function getMediaAndTruncatedMessage(): array
    {
        $message = $this->message;
        $media = [];

        list($images, $message)   = ImageParser::parse($message);
        list($youtubes, $message) = YoutubeParser::parse($message);
        list($videos, $message)   = VideoParser::parse($message);

        $media = [
            'videos'   => array_values($videos),
            'images'   => array_values($images),
            'youtubes' => $youtubes,
        ];

        $message = htmlspecialchars($message, ENT_HTML5);

        return [$media, $message];
    }
}
