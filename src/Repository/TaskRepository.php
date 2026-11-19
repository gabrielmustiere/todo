<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
final class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return list<Task>
     */
    public function findTodo(): array
    {
        /** @var list<Task> */
        return $this
            ->createQueryBuilder('t')
            ->where('t.completedAt IS NULL')
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Task>
     */
    public function findDone(): array
    {
        /** @var list<Task> */
        return $this
            ->createQueryBuilder('t')
            ->where('t.completedAt IS NOT NULL')
            ->orderBy('t.completedAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
