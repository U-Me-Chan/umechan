<?php

namespace PK\Posts;

use PK\Base\IRepository;
use PK\Posts\Post\Post;

interface IPostRepository extends IRepository
{
    public function findOne(array $filters = []): Post;
    public function save(Post $post): int;
    public function delete(Post $post): bool;
}
