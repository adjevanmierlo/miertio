<template>
    <div class="board-list">
        <div class="board-list__header">
            <h1 class="board-list__title">Mijn Boards</h1>
            <button
                class="btn btn--primary"
                @click="showForm = true"
                v-if="!showForm"
            >
                + Nieuw board
            </button>
        </div>

        <form
            v-if="showForm"
            class="board-list__form"
            @submit.prevent="createBoard"
        >
            <input
                v-model="newTitle"
                class="input"
                placeholder="Board naam"
                autofocus
            />
            <div class="board-list__form-actions">
                <button class="btn btn--primary" type="submit">Aanmaken</button>
                <button
                    class="btn btn--ghost"
                    type="button"
                    @click="cancelForm"
                >
                    Annuleren
                </button>
            </div>
        </form>

        <div class="board-list__grid">
            <div v-for="board in boards" :key="board.id" class="board-card">
                <div
                    class="board-card__title"
                    v-if="editingId !== board.id"
                    @click="$emit('select', board)"
                >
                    {{ board.title }}
                </div>
                <input
                    v-else
                    v-model="editTitle"
                    class="input"
                    @keyup.enter="saveEdit(board)"
                    @keyup.escape="cancelEdit"
                    autofocus
                />
                <div class="board-card__actions">
                    <button
                        class="btn btn--ghost btn--sm"
                        @click.stop="startEdit(board)"
                    >
                        ✏️
                    </button>
                    <button
                        class="btn btn--ghost btn--sm"
                        @click.stop="deleteBoard(board)"
                    >
                        🗑️
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { api } from "../api";

const emit = defineEmits(["select"]);

const boards = ref([]);
const showForm = ref(false);
const newTitle = ref("");
const editingId = ref(null);
const editTitle = ref("");

onMounted(async () => {
    boards.value = await api.getBoards();
});

async function createBoard() {
    if (!newTitle.value.trim()) return;
    const board = await api.createBoard({ title: newTitle.value.trim() });
    boards.value.unshift(board);
    cancelForm();
}

function cancelForm() {
    showForm.value = false;
    newTitle.value = "";
}

function startEdit(board) {
    editingId.value = board.id;
    editTitle.value = board.title;
}

async function saveEdit(board) {
    if (!editTitle.value.trim()) return;
    const updated = await api.updateBoard(board.id, {
        title: editTitle.value.trim(),
    });
    board.title = updated.title;
    cancelEdit();
}

function cancelEdit() {
    editingId.value = null;
    editTitle.value = "";
}

async function deleteBoard(board) {
    if (!confirm(`Board "${board.title}" verwijderen?`)) return;
    await api.deleteBoard(board.id);
    boards.value = boards.value.filter((b) => b.id !== board.id);
}
</script>
