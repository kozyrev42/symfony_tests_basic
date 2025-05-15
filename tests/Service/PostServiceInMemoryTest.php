<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PostService;
use App\Tests\Repository\InMemoryPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

// bin/phpunit tests/Service/PostServiceInMemoryTest.php
class PostServiceInMemoryTest extends TestCase
{
    public function testCreatePostWithInMemoryRepo(): void
    {
        $author = new User();
        $author->setName('Author');

        // Присваиваем ID вручную, иначе find() не сработает
        $reflection = new \ReflectionClass($author);
        $prop = $reflection->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($author, 10);

        // Мокаем UserRepository, можно также использовать InMemoryUserRepository
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('find')->willReturn($author);

        // EntityManager мок
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('persist');
        $em->method('flush');

        // Используем InMemoryPostRepository, который хранит данные в памяти
        $inMemoryRepo = new InMemoryPostRepository();

        // !!! Используем InMemoryPostRepository в сервисе
        $service = new PostService($em, $userRepository, $inMemoryRepo);

        $post = $service->createPost([
            'title' => 'In-memory title',
            'content' => 'In-memory content',
            'author_id' => 10,
        ]);

        // Проверки
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('In-memory title', $post->getTitle());
        $this->assertCount(1, $inMemoryRepo->findByAuthorId(10));
    }
}
