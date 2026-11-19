<?php

declare(strict_types=1);

namespace App\Tests\Twig\Components;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Twig\Components\TaskList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;
use Symfony\UX\LiveComponent\Test\TestLiveComponent;

final class TaskListTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    private TaskRepository $taskRepository;
    private EntityManagerInterface $entityManager;
    private TestLiveComponent $component;

    #[\Override]
    protected function setUp(): void
    {
        $container = self::getContainer();

        $taskRepository = $container->get(TaskRepository::class);
        self::assertInstanceOf(TaskRepository::class, $taskRepository);
        $this->taskRepository = $taskRepository;

        $entityManager = $container->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;

        $this->entityManager->createQuery('DELETE FROM ' . Task::class)->execute();

        $userRepository = $container->get(UserRepository::class);
        self::assertInstanceOf(UserRepository::class, $userRepository);
        $user = $userRepository->findOneBy(['email' => 'admin@example.com']);
        self::assertInstanceOf(User::class, $user);

        $this->component = $this->createLiveComponent(TaskList::class)->actingAs($user);
    }

    public function testEmptyStateIsDisplayed(): void
    {
        $crawler = $this->component->render()->crawler();

        self::assertCount(1, $crawler->filter('[data-test="tasks-empty"]'));
        self::assertCount(0, $crawler->filter('[data-test="task-item"]'));
    }

    public function testAddedTaskAppearsOnTopAndInputIsCleared(): void
    {
        $this->component->set('title', 'Première')->call('add');
        $this->component->set('title', '  Seconde  ')->call('add');

        self::assertSame(['Seconde', 'Première'], $this->titlesIn('tasks-todo'));
        $component = $this->component->component();
        self::assertInstanceOf(TaskList::class, $component);
        self::assertSame('', $component->title);
        self::assertCount(0, $this->component->render()->crawler()->filter('[data-test="tasks-empty"]'));
    }

    public function testBlankTitleIsIgnoredWithoutError(): void
    {
        $this->component->set('title', '   ')->call('add');

        self::assertCount(0, $this->taskRepository->findAll());
        self::assertCount(0, $this->component->render()->crawler()->filter('[data-test="task-error"]'));
    }

    public function testTitleIsLimitedTo255CharactersAfterTrim(): void
    {
        $tooLong = str_repeat('a', times: 256);

        $this->component->set('title', $tooLong)->call('add');

        self::assertCount(0, $this->taskRepository->findAll());
        self::assertCount(1, $this->component->render()->crawler()->filter('[data-test="task-error"]'));
        $component = $this->component->component();
        self::assertInstanceOf(TaskList::class, $component);
        self::assertSame($tooLong, $component->title);

        $this->component->set('title', ' ' . str_repeat('a', times: 255) . ' ')->call('add');

        self::assertCount(1, $this->taskRepository->findAll());
        self::assertCount(0, $this->component->render()->crawler()->filter('[data-test="task-error"]'));
    }

    public function testToggleMovesTaskToDoneThenBackToTodo(): void
    {
        $this->component->set('title', 'À cocher')->call('add');
        $id = $this->taskId('À cocher');

        $this->component->call('toggle', ['id' => $id]);
        self::assertSame(['À cocher'], $this->titlesIn('tasks-done'));
        self::assertCount(0, $this->component->render()->crawler()->filter('[data-test="tasks-todo"]'));

        $this->component->call('toggle', ['id' => $id]);
        self::assertSame(['À cocher'], $this->titlesIn('tasks-todo'));
        self::assertCount(0, $this->component->render()->crawler()->filter('[data-test="tasks-done"]'));
    }

    public function testDeleteRemovesTaskAndShowsEmptyState(): void
    {
        $this->component->set('title', 'À supprimer')->call('add');

        $this->component->call('delete', ['id' => $this->taskId('À supprimer')]);

        self::assertCount(0, $this->taskRepository->findAll());
        self::assertCount(1, $this->component->render()->crawler()->filter('[data-test="tasks-empty"]'));
    }

    public function testUnknownTaskIdIsIgnored(): void
    {
        $this->component->set('title', 'Seule')->call('add');
        $unknownId = $this->taskId('Seule') + 1000;

        $this->component->call('toggle', ['id' => $unknownId]);
        $this->component->call('delete', ['id' => $unknownId]);

        self::assertSame(['Seule'], $this->titlesIn('tasks-todo'));
    }

    private function taskId(string $title): int
    {
        $task = $this->taskRepository->findOneBy(['title' => $title]);
        self::assertInstanceOf(Task::class, $task);
        $id = $task->getId();
        self::assertNotNull($id);

        return $id;
    }

    /**
     * @return list<string>
     */
    private function titlesIn(string $section): array
    {
        return array_values($this->component
            ->render()
            ->crawler()
            ->filter(sprintf('[data-test="%s"] [data-test="task-title"]', $section))
            ->each(static fn(Crawler $node): string => $node->text()));
    }
}
