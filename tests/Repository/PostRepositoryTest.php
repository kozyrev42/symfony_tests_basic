<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Пример интеграционного теста/
 * 
 * Интеграционные тесты проверяют взаимодействие нескольких компонентов системы вместе.
 * 
 * В интеграционном тесте используются внешние ресурсы, например, база данных.
 * 
 * Когда писать интеграционные тесты?
 * 1) Когда нужно проверить работу реальных компонентов в связке (например, Entity + Repository + DB).
 * 2) Когда мокать всё слишком сложно и нужен реализм, но не весь стек, как в E2E.
 * 
 * bin/phpunit tests/Repository/PostRepositoryTest.php
 */
class PostRepositoryTest extends KernelTestCase
{
    private PostRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repo = self::getContainer()->get(PostRepository::class);
    }

    public function testFindByAuthorId(): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        // создаём автора и пост прямо здесь
        $author = (new User())->setName('Tester')->setEmail('t@test.local');
        $post   = (new Post())->setTitle('Integration')->setContent('Text')->setAuthor($author);

        $em->persist($author);
        $em->persist($post);
        $em->flush();

        // проверяем метод
        $found = $this->repo->findByAuthorId($author->getId());

        $this->assertCount(1, $found);
        $this->assertSame($post->getId(), $found[0]->getId());
    }

    public function testCommentChain(): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $author = (new User())->setName('Alice')->setEmail('a@test.local');
        $post   = (new Post())->setTitle('Chain')->setContent('X')->setAuthor($author);
        $comment = (new Comment())->setText('Nice!')->setPost($post);

        $post->addComment($comment);

        $em->persist($author);
        $em->persist($post);
        $em->persist($comment);
        $em->flush();

        // перезагружаем пост и проверяем связь
        $refreshed = $this->repo->findPostById($post->getId());

        $this->assertSame('Alice', $refreshed->getAuthor()->getName());
        $this->assertCount(1, $refreshed->getComments());
        $this->assertEquals('Nice!', $refreshed->getComments()->first()->getText());
    }
}
