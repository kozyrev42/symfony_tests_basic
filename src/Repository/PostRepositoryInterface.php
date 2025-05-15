<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\DBAL\LockMode;

interface PostRepositoryInterface
{
    public function save(Post $post): void;

    public function findByAuthorId(int $authorId): array;

    public function findPostById(int $id): ?Post;
}
