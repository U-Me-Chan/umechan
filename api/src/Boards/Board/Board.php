<?php

namespace PK\Boards\Board;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Board')]
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
        #[OA\Property(description: 'Идентификатор доски', type: 'integer')]
        public int $id,
        #[OA\Property(description: 'Тег доски', type: 'string')]
        public string $tag,
        #[OA\Property(description: 'Имя доски', type: 'string')]
        public string $name,
        #[OA\Property(description: 'Количество тредов на доске', type: 'integer')]
        public int $threads_count,
        #[OA\Property(description: 'Количество новых постов за сутки', type: 'integer')]
        public int $new_posts_count
    ) {
    }
}
