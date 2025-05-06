<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    // создание комментария
    #[Route('/comment', name: 'create_comment', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, PostRepository $postRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['text'], $data['post_id'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $post = $postRepository->find($data['post_id']);
        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $comment = new Comment();
        $comment->setText($data['text']);
        $comment->setPost($post);

        $em->persist($comment);
        $em->flush();

        return $this->json([
            'id' => $comment->getId(),
            'text' => $comment->getText(),
            'post_id' => $post->getId(),
        ], 201);
    }
}
