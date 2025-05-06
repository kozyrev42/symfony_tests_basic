<?php

namespace App\Service;

use App\Repository\PostRepository;
use App\Entity\Post;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private PostRepository $postRepository,
    ){}

    public function createPost(array $data): array
    {
        if (!isset($data['title'], $data['content'], $data['author_id'])) {
            throw new BadRequestHttpException('Missing required fields');
        }

        $author = $this->userRepository->find($data['author_id']);
        if (!$author) {
            throw new NotFoundHttpException('Author not found');
        }

        $post = new Post();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);
        $post->setAuthor($author);

        $this->em->persist($post);
        $this->em->flush();

        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'author_id' => $author->getId(),
        ];
    }

    public function getPostWithComments(int $postId): array
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new NotFoundHttpException('Пост не найден');
        }

        $comments = [];
        foreach ($post->getComments() as $comment) {
            $comments[] = [
                'id' => $comment->getId(),
                'text' => $comment->getText(),
            ];
        }

        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'author' => $post->getAuthor()?->getName(),
            'comments' => $comments,
        ];
    }

    public function getPostsByAuthor(int $authorId): array
    {
        $posts = $this->postRepository->findByAuthorId($authorId);

        $result = [];

        foreach ($posts as $post) {
            $comments = [];
            foreach ($post->getComments() as $comment) {
                $comments[] = [
                    'id' => $comment->getId(),
                    'text' => $comment->getText(),
                ];
            }

            $result[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'author' => $post->getAuthor()?->getName(),
                'comments' => $comments,
            ];
        }

        return $result;
    }
}
