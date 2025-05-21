<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Функциональные тесты эндпоинтов PostController.
 * Работаем настоящим HTTP-клиентом, база «test_auto» откатывается
 * DAMADoctrineTestBundle-ом после каждого теста.
 * 
 * Проверил: контроллер вызывается и возвращает ответ!
 * 
 * bin/phpunit tests/Controller/PostControllerTest.php
 */
class PostControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private int $authorId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em     = self::getContainer()->get('doctrine')->getManager();

        // создаём тестового автора
        $author = (new User())
            ->setName('HTTP-Author')
            ->setEmail('http@test.local');

        $this->em->persist($author);
        $this->em->flush();
        $this->authorId = $author->getId();
    }

    /** ✔️ happy-path: POST /post */
    public function testCreatePost(): void
    {
        $this->client->request(
            'POST',
            '/post',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title'     => 'HTTP post',
                'content'   => 'text',
                'author_id' => $this->authorId,
            ])
        );

        // ожидаем 201 Created
        $this->assertResponseStatusCodeSame(201);

        // JSON должен содержать наши данные
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('HTTP post',   $data['title']);
        $this->assertSame($this->authorId, $data['author_id']);
    }

    /** ❌ валидация: отсутствуют обязательные поля */
    public function testCreatePostValidationError(): void
    {
        $this->client->request(
            'POST',
            '/post',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'no content'])
        );

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Missing required fields', $data['error']);
    }

    /** ✔️ GET /posts/{user_id} возвращает посты пользователя */
    public function testGetPostsByAuthor(): void
    {
        // создаём пост (переиспользуем предыдущий тест)
        $this->testCreatePost();

        $this->client->request('GET', "/posts/{$this->authorId}");
        $this->assertResponseIsSuccessful();

        $list = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $list);
        $this->assertSame('HTTP post', $list[0]['title']);
    }

    /** ❌ 404: запрашиваем несуществующий пост */
    public function testGetPostNotFound(): void
    {
        $this->client->request('GET', '/post/999999');
        $this->assertResponseStatusCodeSame(404);
    }
}
