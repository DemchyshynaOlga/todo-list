import { createApp } from 'vue';
import TodoList from './components/TodoList.vue';

const modalElement = document.getElementById('todo-modal-app');
if (modalElement) {
    const app = createApp(TodoList);
    app.mount('#todo-modal-app');
}
