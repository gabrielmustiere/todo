<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;

final readonly class TaskManager
{
    public function __construct(
        private TaskRepository $taskRepository,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
    ) {}

    public function add(string $title): void
    {
        $title = trim($title);
        if ('' === $title) {
            return;
        }

        $this->entityManager->persist(new Task($title, $this->clock->now()));
        $this->entityManager->flush();
    }

    public function toggle(int $id): void
    {
        $task = $this->taskRepository->find($id);
        if (null === $task) {
            return;
        }

        $task->setCompletedAt(null === $task->getCompletedAt() ? $this->clock->now() : null);
        $this->entityManager->flush();
    }

    public function delete(int $id): void
    {
        $task = $this->taskRepository->find($id);
        if (null === $task) {
            return;
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}
