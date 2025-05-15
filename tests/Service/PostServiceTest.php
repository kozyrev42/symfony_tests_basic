<?php

namespace App\Tests\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepositoryInterface;
use App\Repository\UserRepository;
use App\Service\PostService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// bin/phpunit tests/Service/PostServiceTest.php
class PostServiceTest extends TestCase
{
    /**
     * ✅ Успешное создание поста с корректными данными.
     */
    public function testCreatePostSuccess(): void
    {
        // 👉 Входные данные для создания поста
        $authorId = 1;
        $title = 'Test title';
        $content = 'Test content';

        // 🧪 Заглушка User (автор поста)
        $author = new User();
        $author->setName('Test Author');

        // 🧪 Мок UserRepository — возвращает заглушку User по ID
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('find')
            ->with($authorId)
            ->willReturn($author);

        // 🧪 Мок EntityManager — без ожиданий (persist/flush вызываются внутри репозитория)
        $em = $this->createMock(EntityManagerInterface::class);

        // 🧪 Мок PostRepositoryInterface — ожидаем вызов save()
        $postRepository = $this->createMock(PostRepositoryInterface::class);
        $postRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Post::class));

        // ⚙️ Создание сервиса
        $service = new PostService($em, $userRepository, $postRepository);

        // 🎯 Вызов метода
        $post = $service->createPost([
            'title' => $title,
            'content' => $content,
            'author_id' => $authorId,
        ]);

        // ✅ Проверка результата:
        // Убедимся, что результат $post — это объект класса Post
        $this->assertInstanceOf(Post::class, $post);

        // Что поле title в созданном посте совпадает с тем, что мы передали на вход.
        $this->assertEquals($title, $post->getTitle());
        // То же самое, но для поля content
        $this->assertEquals($content, $post->getContent());
        // Что в поле author установлен тот самый объект User, который мы передали
        $this->assertSame($author, $post->getAuthor());
    }

    /**
     * ❌ Ошибка: отсутствуют обязательные поля.
     */
    public function testCreatePostMissingFields(): void
    {
        // 🧪 Моки
        $em = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $postRepository = $this->createMock(PostRepositoryInterface::class);

        // ⚙️ Сервис
        $service = new PostService($em, $userRepository, $postRepository);

        // expectException() и expectExceptionMessage() - должен идти до вызова метода,
        // который должен выбросить исключение
        // - можно их закоментировать, тогда исключения буду выброшены и Тест упадёт.
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Missing required fields');

        // 🎯 Вызов метода с пропущенными полями
        $service->createPost([
            'title' => 'Some title',
            // 'content' отсутствует
            // 'author_id' отсутствует
        ]);
    }

    /**
     * ❌ Ошибка: автор не найден в базе.
     */
    public function testCreatePostAuthorNotFound(): void
    {
        // 👉 Данные
        $authorId = 999;

        // 🧪 Моки: UserRepository возвращает null
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('find')
            ->with($authorId)
            ->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $postRepository = $this->createMock(PostRepositoryInterface::class);

        // ⚙️ Сервис
        $service = new PostService($em, $userRepository, $postRepository);

        // expectException() и expectExceptionMessage() - должен идти до вызова метода,
        // который должен выбросить исключение
        // - можно их закоментировать, тогда исключения буду выброшены и Тест упадёт.
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Author not found');

        // 🎯 Вызов метода с несуществующим автором
        $service->createPost([
            'title' => 'Some title',
            'content' => 'Some content',
            'author_id' => $authorId,
        ]);
    }
}
