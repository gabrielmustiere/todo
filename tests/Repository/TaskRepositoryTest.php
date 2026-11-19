<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TaskRepositoryTest extends KernelTestCase
{
    private TaskRepository $taskRepository;
    private EntityManagerInterface $entityManager;

    #[\Override]
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $taskRepository = $container->get(TaskRepository::class);
        self::assertInstanceOf(TaskRepository::class, $taskRepository);
        $this->taskRepository = $taskRepository;

        $entityManager = $container->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;

        $this->entityManager->createQuery('DELETE FROM ' . Task::class)->execute();

        $this->persistTask('Ancienne à faire', '2026-11-17 09:00');
        $this->persistTask('Récente à faire', '2026-11-19 09:00');
        $this->persistTask('Faite tôt', '2026-11-16 09:00', '2026-11-18 08:00');
        $this->persistTask('Faite tard', '2026-11-18 09:00', '2026-11-19 08:00');
        $this->persistTask('Même seconde, créée après', '2026-11-19 09:00');
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function testFindTodoReturnsOnlyOpenTasksNewestFirst(): void
    {
        self::assertSame(
            ['Même seconde, créée après', 'Récente à faire', 'Ancienne à faire'],
            $this->titles($this->taskRepository->findTodo()),
        );
    }

    public function testFindDoneReturnsOnlyCompletedTasksLastCompletedFirst(): void
    {
        self::assertSame(['Faite tard', 'Faite tôt'], $this->titles($this->taskRepository->findDone()));
    }

    private function persistTask(string $title, string $createdAt, ?string $completedAt = null): void
    {
        $task = new Task($title, new \DateTimeImmutable($createdAt));
        if (null !== $completedAt) {
            $task->setCompletedAt(new \DateTimeImmutable($completedAt));
        }

        $this->entityManager->persist($task);
    }

    /**
     * @param list<Task> $tasks
     *
     * @return list<string>
     */
    private function titles(array $tasks): array
    {
        return array_map(static fn(Task $task): string => $task->getTitle(), $tasks);
    }
}
