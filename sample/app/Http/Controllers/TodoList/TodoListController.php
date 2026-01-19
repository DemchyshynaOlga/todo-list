<?php

declare(strict_types=1);

namespace App\Http\Controllers\TodoList;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TodoListController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {
    }

    public function index(): View
    {
        $tasks = $this->taskService->getTasksGroupedByStatus();
        
        return view('todolist.index', compact('tasks'));
    }

    public function create(CreateTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json($task, 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->taskService->updateTask($task, $request->validated());

        return response()->json($task);
    }

    public function delete(Task $task): JsonResponse
    {
        $this->taskService->deleteTask($task);
        
        return response()->json(['message' => 'Task deleted successfully']);
    }
}
