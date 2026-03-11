<?php

namespace PK\Boards;

use InvalidArgumentException;
use OpenApi\Attributes as OA;
use PK\Boards\Board\PublicFlag;

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
            0,
            PublicFlag::yes
        );
    }

    /**
     * @param array{
     *     id?: int,
     *     board_id?: int,
     *     tag: string,
     *     name: string,
     *     threads_count?: int,
     *     new_posts_count?: int,
     *     is_public?: 'yes'|'no'
     * } $state
     */
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
            $state['new_posts_count'] ?? 0,
            PublicFlag::fromString($state['is_public'] ?? PublicFlag::yes->name)
        );
    }

    /**
     * @return array{
     *     id: int,
     *     tag: string,
     *     name: string,
     *     threads_count: positive-int,
     *     new_posts_count: positive-int,
     *     is_public: bool
     * }
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return array{
     *     id: int,
     *     tag: string,
     *     name: string,
     *     threads_count: positive-int,
     *     new_posts_count: positive-int,
     *     is_public: 'yes'|'no'
     * }
     */
    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'tag'             => $this->tag,
            'name'            => $this->name,
            'threads_count'   => $this->threads_count,
            'new_posts_count' => $this->new_posts_count,
            'is_public'       => $this->is_public->name
        ];
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
        public int $new_posts_count,
        #[OA\Property(description: 'Доска публичная?', type: 'boolean')]
        public PublicFlag $is_public
    ) {
    }
}
