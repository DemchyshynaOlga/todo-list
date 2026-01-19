<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Collection;

class TaskService
{
    public function getTasksGroupedByStatus(): Collection
    {
        return Task::orderBy('order')->get()->groupBy('status');
    }

    public function createTask(array $data): Task
    {
        $data['order'] = $this->getNextOrderForStatus($data['status']);

        return Task::create($data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }

    private function getNextOrderForStatus(string $status): int
    {
        $maxOrder = Task::where('status', $status)->max('order');

        return ($maxOrder ?? 0) + 1;
    }
}
