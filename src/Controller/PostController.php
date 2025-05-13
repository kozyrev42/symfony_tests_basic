<?php

namespace App\Controller;

use App\Service\PostService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    // создание поста
    #[Route('/post', name: 'create_post', methods: ['POST'])]
    public function create(Request $request, PostService $postService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        try {
            $post = $postService->createPost($data);
            return $this->json([
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'author_id' => $post->getAuthor()->getId(),
            ], 201);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500);
        }
    }

    // получение постов, по user_id
    #[Route('/posts/{user_id}', name: 'get_user_posts', methods: ['GET'])]
    public function list(int $user_id, PostService $postService): JsonResponse
    {
        $data = $postService->getPostsByAuthor($user_id);
        return $this->json($data);
    }

    // получение поста по id поста
    #[Route('/post/{id}', name: 'get_post', methods: ['GET'])]
    public function getPost(int $id, PostService $postService): JsonResponse
    {
        $data = $postService->getPostWithComments($id);
        return $this->json($data);
    }
}
