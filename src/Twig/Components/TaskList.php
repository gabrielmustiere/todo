<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\TaskManager;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;

#[AsLiveComponent]
final class TaskList
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp(writable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Une tâche fait {{ limit }} caractères au plus.', normalizer: 'trim')]
    public string $title = '';

    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly TaskManager $taskManager,
    ) {}

    #[LiveAction]
    public function add(): void
    {
        $this->validate(throw: false);
        if (!$this->isValid()) {
            return;
        }

        $this->taskManager->add($this->title);
        $this->title = '';
        $this->clearValidation();
    }

    #[LiveAction]
    public function toggle(#[LiveArg] int $id): void
    {
        $this->taskManager->toggle($id);
    }

    #[LiveAction]
    public function delete(#[LiveArg] int $id): void
    {
        $this->taskManager->delete($id);
    }

    /**
     * @return list<Task>
     */
    public function getTodoTasks(): array
    {
        return $this->taskRepository->findTodo();
    }

    /**
     * @return list<Task>
     */
    public function getDoneTasks(): array
    {
        return $this->taskRepository->findDone();
    }
}
