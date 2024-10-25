<?php

namespace PK\Boards\Board;

use OpenApi\Attributes as OA;

#[OA\Schema]
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
        #[OA\Property]
        public int $id,
        #[OA\Property]
        public string $tag,
        #[OA\Property]
        public string $name,
        #[OA\Property]
        public int $threads_count,
        #[OA\Property]
        public int $new_posts_count
    ) {
    }
}
