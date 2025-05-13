<?php

namespace App\Tests\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
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

        // 🧪 Мок EntityManager — ожидаем вызов persist() и flush()
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(Post::class));
        $em->expects($this->once())->method('flush');

        // 🧪 Мок PostRepository — не используется в этом тесте, но нужен для конструктора
        $postRepository = $this->createMock(PostRepository::class);

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
        $postRepository = $this->createMock(PostRepository::class);

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
        $postRepository = $this->createMock(PostRepository::class);

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
