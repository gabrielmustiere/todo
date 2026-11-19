<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\TaskManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;

final class TaskManagerTest extends KernelTestCase
{
    private MockClock $clock;
    private TaskManager $taskManager;
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

        $this->clock = new MockClock('2026-11-19 10:00:00');
        $this->taskManager = new TaskManager($this->taskRepository, $this->entityManager, $this->clock);

        $this->entityManager->createQuery('DELETE FROM ' . Task::class)->execute();
    }

    public function testAddTrimsTitleAndStampsCreationDate(): void
    {
        $this->taskManager->add('  Acheter du pain  ');
        $this->entityManager->clear();

        self::assertCount(1, $this->taskRepository->findAll());
        $task = $this->taskRepository->findOneBy([]);
        self::assertInstanceOf(Task::class, $task);
        self::assertSame('Acheter du pain', $task->getTitle());
        self::assertEquals(new \DateTimeImmutable('2026-11-19 10:00:00'), $task->getCreatedAt());
        self::assertNull($task->getCompletedAt());
    }

    public function testAddIgnoresEmptyTitle(): void
    {
        $this->taskManager->add('');
        $this->taskManager->add('   ');

        self::assertCount(0, $this->taskRepository->findAll());
    }

    public function testToggleCompletesThenReopensTask(): void
    {
        $id = $this->createTask('Appeler le garage');

        $this->clock->modify('2026-11-19 11:30:00');
        $this->taskManager->toggle($id);
        self::assertEquals(new \DateTimeImmutable('2026-11-19 11:30:00'), $this->findTask($id)->getCompletedAt());

        $this->taskManager->toggle($id);
        self::assertNull($this->findTask($id)->getCompletedAt());
    }

    public function testDeleteRemovesTask(): void
    {
        $id = $this->createTask('Relire le contrat');

        $this->taskManager->delete($id);

        self::assertNull($this->taskRepository->find($id));
    }

    public function testUnknownTaskIsIgnored(): void
    {
        $id = $this->createTask('Tâche existante');

        $this->taskManager->toggle($id + 1000);
        $this->taskManager->delete($id + 1000);

        self::assertNull($this->findTask($id)->getCompletedAt());
        self::assertCount(1, $this->taskRepository->findAll());
    }

    private function createTask(string $title): int
    {
        $this->taskManager->add($title);
        $task = $this->taskRepository->findOneBy(['title' => $title]);
        self::assertInstanceOf(Task::class, $task);
        $id = $task->getId();
        self::assertNotNull($id);

        return $id;
    }

    private function findTask(int $id): Task
    {
        $this->entityManager->clear();
        $task = $this->taskRepository->find($id);
        self::assertInstanceOf(Task::class, $task);

        return $task;
    }
}
