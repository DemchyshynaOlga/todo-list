<div class="task-card" data-task-id="{{ $task->id }}" draggable="true"
    ondragstart="handleDragStart(event, {{ $task->id }})" ondragend="handleDragEnd(event)"
    ondragleave="handleDragLeave(event)">
    <div class="task-title">{{ $task->title }}</div>
    @if ($task->description)
        <div class="task-description">{{ $task->description }}</div>
    @endif
    <div class="task-meta">
        <span>{{ $task->created_at->format('M d, Y') }}</span>
    </div>
</div>
