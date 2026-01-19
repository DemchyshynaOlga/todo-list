<template>
    <div class="modal" :class="{ active: showModal }" @click.self="closeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Task</h2>
                <button class="modal-close" @click="closeModal" type="button">&times;</button>
            </div>
            <form @submit.prevent="createTask">
                <div class="form-group">
                    <label for="taskTitle">Title *</label>
                    <input 
                        type="text" 
                        id="taskTitle" 
                        v-model="newTask.title" 
                        required
                        :disabled="isLoading"
                    >
                </div>
                <div class="form-group">
                    <label for="taskDescription">Description</label>
                    <textarea 
                        id="taskDescription" 
                        v-model="newTask.description"
                        :disabled="isLoading"
                    ></textarea>
                </div>
                <div class="form-group">
                    <label for="taskStatus">Status *</label>
                    <select 
                        id="taskStatus" 
                        v-model="newTask.status" 
                        required
                        :disabled="isLoading"
                    >
                        <option value="todo">TO DO</option>
                        <option value="inprogress">IN PROGRESS</option>
                        <option value="done">DONE</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button 
                        type="button" 
                        class="btn btn-secondary" 
                        @click="closeModal"
                        :disabled="isLoading"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="btn btn-primary"
                        :disabled="isLoading"
                    >
                        {{ isLoading ? 'Creating...' : 'Create Task' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TodoList',
    data() {
        return {
            showModal: false,
            isLoading: false,
            newTask: {
                title: '',
                description: '',
                status: 'todo'
            },
            csrfToken: null,
            eventListeners: []
        };
    },
    mounted() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.setupDragAndDrop();
        this.setupCreateButton();
    },
    beforeUnmount() {
        this.eventListeners.forEach(({ element, event, handler }) => {
            element.removeEventListener(event, handler);
        });
        this.eventListeners = [];
    },
    methods: {
        setupCreateButton() {
            const createBtn = document.getElementById('create-task-btn');
            if (createBtn) {
                const handler = () => {
                    this.showModal = true;
                };
                createBtn.addEventListener('click', handler);
                this.eventListeners.push({ element: createBtn, event: 'click', handler });
            }
        },
        setupDragAndDrop() {
            const taskCards = document.querySelectorAll('.task-card');
            const columns = document.querySelectorAll('.kanban-column');

            taskCards.forEach(card => {
                const dragStartHandler = (e) => {
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', card.dataset.taskId);
                    card.classList.add('dragging');
                };

                const dragEndHandler = () => {
                    card.classList.remove('dragging');
                };

                card.addEventListener('dragstart', dragStartHandler);
                card.addEventListener('dragend', dragEndHandler);
                
                this.eventListeners.push(
                    { element: card, event: 'dragstart', handler: dragStartHandler },
                    { element: card, event: 'dragend', handler: dragEndHandler }
                );
            });

            columns.forEach(column => {
                const dragOverHandler = (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                };

                const dropHandler = async (e) => {
                    e.preventDefault();
                    await this.handleTaskDrop(e, column);
                };

                column.addEventListener('dragover', dragOverHandler);
                column.addEventListener('drop', dropHandler);
                
                this.eventListeners.push(
                    { element: column, event: 'dragover', handler: dragOverHandler },
                    { element: column, event: 'drop', handler: dropHandler }
                );
            });
        },
        async handleTaskDrop(event, column) {
            const taskId = event.dataTransfer.getData('text/plain');
            const newStatus = column.dataset.status;
            
            const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
            if (!taskCard) return;

            const oldStatus = taskCard.dataset.status;
            const taskList = column.querySelector('.task-list');
            const oldColumn = document.querySelector(`[data-status="${oldStatus}"]`);
            const oldTaskList = oldColumn?.querySelector('.task-list');
            
            const emptyState = taskList.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }

            taskCard.dataset.status = newStatus;
            taskList.appendChild(taskCard);

            if (oldTaskList && oldTaskList.querySelectorAll('.task-card').length === 0) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'empty-state';
                emptyDiv.textContent = 'No tasks';
                oldTaskList.appendChild(emptyDiv);
            }

            try {
                const response = await fetch(`/tasks/${taskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        status: newStatus,
                    }),
                });

                if (!response.ok) {
                    this.revertTaskMove(taskCard, oldStatus, oldTaskList, taskList);
                    throw new Error('Failed to update task');
                }

                this.updateColumnCounts();
            } catch (error) {
                console.error('Error updating task:', error);
                this.revertTaskMove(taskCard, oldStatus, oldTaskList, taskList);
                alert('Error updating task. Please try again.');
            }
        },
        revertTaskMove(taskCard, oldStatus, oldTaskList, newTaskList) {
            if (oldTaskList) {
                const oldEmptyState = oldTaskList.querySelector('.empty-state');
                if (oldEmptyState) {
                    oldEmptyState.remove();
                }
                
                oldTaskList.appendChild(taskCard);
                taskCard.dataset.status = oldStatus;

                if (newTaskList.querySelectorAll('.task-card').length === 0) {
                    const emptyDiv = document.createElement('div');
                    emptyDiv.className = 'empty-state';
                    emptyDiv.textContent = 'No tasks';
                    newTaskList.appendChild(emptyDiv);
                }
            }
        },
        updateColumnCounts() {
            const counts = {
                todo: document.querySelector('#todo-list')?.querySelectorAll('.task-card').length || 0,
                inprogress: document.querySelector('#inprogress-list')?.querySelectorAll('.task-card').length || 0,
                done: document.querySelector('#done-list')?.querySelectorAll('.task-card').length || 0,
            };

            const todoCount = document.getElementById('count-todo');
            const inprogressCount = document.getElementById('count-inprogress');
            const doneCount = document.getElementById('count-done');
            
            if (todoCount) todoCount.textContent = counts.todo;
            if (inprogressCount) inprogressCount.textContent = counts.inprogress;
            if (doneCount) doneCount.textContent = counts.done;
        },
        closeModal() {
            if (this.isLoading) return;
            
            this.showModal = false;
            this.resetForm();
        },
        resetForm() {
            this.newTask = {
                title: '',
                description: '',
                status: 'todo'
            };
        },
        async createTask() {
            if (this.isLoading) return;

            this.isLoading = true;

            try {
                const response = await fetch('/tasks', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.newTask),
                });

                if (response.ok) {
                    this.closeModal();
                    window.location.reload();
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    alert(errorData.message || 'Error creating task');
                }
            } catch (error) {
                console.error('Error creating task:', error);
                alert('Error creating task. Please try again.');
            } finally {
                this.isLoading = false;
            }
        }
    }
};
</script>
