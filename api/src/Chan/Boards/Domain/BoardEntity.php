<?php

namespace PK\Chan\Boards\Domain;

use PK\Shared\Domain\Entity;
use PK\Chan\Boards\Domain\BoardId;
use PK\Chan\Boards\Domain\BoardName;
use PK\Chan\Boards\Domain\BoardNewPostCounter;
use PK\Chan\Boards\Domain\BoardTag;
use PK\Chan\Boards\Domain\BoardThreadCounter;

class BoardEntity extends Entity
{
    public static function createDraft(BoardTag $tag, BoardName $name): self
    {
        return new self(new BoardId(0), $tag, $name, new BoardThreadCounter(0), new BoardNewPostCounter(0));
    }

    public static function createFromArray(array $state): self
    {
        return new self(
            new BoardId($state['id']),
            new BoardTag($state['tag']),
            new BoardName($state['name']),
            new BoardThreadCounter($state['threas_count']),
            new BoardNewPostCounter($state['new_post_count'])
        );
    }

    public function changeName(string $name): void
    {
        $this->name = $name;
    }

    public function changeTag(string $tag): void
    {
        $this->tag = $tag;
    }

    public function bumpThreadCounter(): void
    {
        $this->threads_count->bump();
    }

    public function bumpNewPostCounter(): void
    {
        $this->new_posts_count->bump();
    }

    public function getId(): int
    {
        return $this->id->toInt();
    }

    public function getTag(): string
    {
        return $this->tag->toString();
    }

    public function getName(): string
    {
        return $this->name->toString();
    }

    public function getThreadsCount(): int
    {
        return $this->threads_count->toInt();
    }

    public function getNewPostsCount(): int
    {
        return $this->new_posts_count->toInt();
    }

    private function __construct(
        private readonly BoardId $id,
        private BoardTag $tag,
        private BoardName $name,
        private BoardThreadCounter $threads_count,
        private BoardNewPostCounter $new_posts_count
    ) {
    }
}
