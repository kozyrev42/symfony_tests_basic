<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Функциональные тесты эндпойнта CommentController.
 * Работаем настоящим HTTP-клиентом, база «test_auto» откатывается
 * DAMADoctrineTestBundle-ом после каждого теста.
 * 
 * Проверил: контроллер вызывается и возвращает ответ!
 * 
 * bin/phpunit tests/Controller/CommentControllerTest.php
 */
class CommentControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private int $postId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em     = self::getContainer()->get('doctrine')->getManager();

        // автор + пост для комментариев
        $author = (new User())->setName('Com')->setEmail('c@test.local');
        $post   = (new Post())->setTitle('for comment')->setContent('txt')->setAuthor($author);

        $this->em->persist($author);
        $this->em->persist($post);
        $this->em->flush();

        $this->postId = $post->getId();
    }

    /** ✔️ happy-path: POST /comment */
    public function testCreateComment(): void
    {
        $this->client->request(
            'POST',
            '/comment',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'text'    => 'Nice!',
                'post_id' => $this->postId,
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Nice!', $data['text']);
        $this->assertSame($this->postId, $data['post_id']);
    }

    /** ❌ 404: пост для комментария не найден */
    public function testCreateCommentPostNotFound(): void
    {
        $this->client->request(
            'POST',
            '/comment',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'text'    => 'bad',
                'post_id' => 123456,           // несуществующий ID
            ])
        );

        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Post not found', $data['error']);
    }
}
