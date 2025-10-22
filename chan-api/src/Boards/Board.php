<?php

namespace PK\Boards;

use InvalidArgumentException;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Board')]
class Board implements \JsonSerializable
{
    public static function draft(string $tag, string $name): self
    {
        return new self(
            0,
            $tag,
            $name,
            0,
            0
        );
    }

    public static function fromArray(array $state): self
    {
        $id = match(true) {
            isset($state['id'])       => $state['id'],
            isset($state['board_id']) => $state['board_id'],
            default                   => throw new InvalidArgumentException()
        };


        return new self(
            $id,
            $state['tag'],
            $state['name'],
            $state['threads_count'] ?? 0,
            $state['new_posts_count'] ?? 0
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
        #[OA\Property(description: 'Идентификатор')]
        public int $id,
        #[OA\Property(description: 'Тег')]
        public string $tag,
        #[OA\Property(description: 'Имя')]
        public string $name,
        #[OA\Property(description: 'Общее количество тем на доске')]
        public int $threads_count,
        #[OA\Property(description: 'Количество постов за сутки')]
        public int $new_posts_count
    ) {
    }
}
