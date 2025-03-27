<?php

namespace PK\Database\Board;

class Board
{
    public function __construct(
        private int $id,
        private string $tag,
        private string $name
    ) {
    }

    public static function fromState(array $state): self
    {
        return new self(
            $state['id'],
            $state['tag'],
            $state['name']
        );
    }

    public function getId(): int
    {
        if (!$this->id) {
            throw new \RuntimeException("Эта доска ещё не была создана");
        }

        return $this->id;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tag' => $this->tag,
            'name' => $this->name
        ];
    }
}
