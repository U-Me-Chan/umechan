<?php

namespace PK\Boards\Board;

class Board implements \JsonSerializable
{
    public static function draft()
    {
        throw new \RuntimeException();
    }

    public static function fromArray(array $state): self
    {
        if (isset($state['board_id'])) {
            $id = $state['board_id'];
        } else if (isset($state['id'])) {
            $id = $state['id'];
        } else {
            throw new \InvalidArgumentException();
        }

        return new self(
            $id,
            $state['tag'],
            $state['name'],
            $state['threads_count'],
            $state['new_posts_count']
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    private function __construct(
        public int $id,
        public string $tag,
        public string $name,
        public int $threads_count,
        public int $new_posts_count
    ) {
    }
}
