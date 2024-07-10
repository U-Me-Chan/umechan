<?php

namespace PK\Infrastructure;

use PK\Domain\Post;

interface IPostRepository extends IRepository
{
    public function save(Post $post): int;
}
