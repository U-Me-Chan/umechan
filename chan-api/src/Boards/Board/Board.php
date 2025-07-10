<?php

namespace PK\Boards\Board;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Board')]
class Board implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    public static function fromArray(array $state): self
    {
        return new self(
            $state['id'],
            $state['tag'],
            $state['name'],
            $state['threads_count'],
            $state['new_posts_count']
        );
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
