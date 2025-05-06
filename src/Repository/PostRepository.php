<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий (Repository) — это слой, отвечающий за доступ к данным.
 * Он инкапсулирует всю логику получения, сохранения, поиска и удаления сущностей в БД.
 * 
 * В контексте Doctrine (и DDD — Domain-Driven Design) -
 * это объект-посредник между доменной логикой и базой данных.
 * 
 * 
 * Цель репозиториев:
 * — Абстрагировать работу с базой данных.
 * — Инкапсулировать запросы: ты не пишешь SQL-запросы прямо в контроллере или сервисе.
 * — Централизовать логику получения данных, особенно если это кастомные или сложные запросы.
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Метод для поиска постов по ID автора
     *
     * @param int $authorId
     * @return Post[]
     */
    public function findByAuthorId(int $authorId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :authorId')
            ->setParameter('authorId', $authorId)
            ->getQuery()
            ->getResult();
    }
}
