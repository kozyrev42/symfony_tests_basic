<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Post;
use App\Repository\PostRepositoryInterface;

class InMemoryPostRepository implements PostRepositoryInterface
{
    private array $posts = [];

    public function save(Post $post): void { $this->posts[] = $post; }

    public function findByAuthorId(int $authorId): array
    {
        return array_filter($this->posts, fn(Post $post) => $post->getAuthor()?->getId() === $authorId);
    }

    public function findPostById(int $id): ?Post
    {
        foreach ($this->posts as $post) {
            if ($post->getId() === $id) {
                return $post;
            }
        }

        return null;
    }
}
