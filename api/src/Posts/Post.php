<?php

namespace PK\Posts;

use PK\Boards\Board\Board;
use PK\Posts\Post\ImageParser;
use PK\Posts\Post\VideoParser;
use PK\Posts\Post\YoutubeParser;

class Post implements \JsonSerializable
{
    public static function draft(
        Board $board,
        int|null $parent_id,
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
            hash('sha256', bin2hex(random_bytes(5)))
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
            $state['password'],
            !empty($state['replies']) ? $state['replies'] : [],
            isset($state['replies_count']) ? $state['replies_count'] : 0,
            $state['is_verify'] == 'yes' ? true : false
        );
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);
        $data['board_id'] = $data['board']->id;

        list($media, $truncated_message) = $this->getMediaAndTruncatedMessage();

        $data['media'] = $media;
        $data['truncated_message'] = $truncated_message;
        $data['datetime'] =  date('Y-m-d G:i:s', $data['timestamp'] + 60 * (60 * 4));

        unset($data['password']);

        return $data;
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);
        $data['board_id'] = $data['board']->id;

        unset($data['board'], $data['replies'], $data['replies_count'], $data['is_verify']);

        return $data;
    }

    private function __construct(
        public int $id,
        public string $poster,
        public string $subject,
        public string $message,
        public int $timestamp,
        public Board $board,
        public int|null $parent_id,
        public int $updated_at,
        public int $estimate,
        public string $password,
        public array $replies = [],
        public int $replies_count = 0,
        public bool $is_verify = false
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

        return [$media, $message];
    }
}
