<template>
    <div class="home">
        <div class="home__header">
            <h1 class="home__title">Boards</h1>
            <button
                class="btn btn--primary"
                @click="showForm = true"
                v-if="!showForm"
            >
                + Nieuw board
            </button>
        </div>

        <div v-if="showForm" class="home__form">
            <input
                v-model="newTitle"
                class="input"
                placeholder="Board naam"
                autofocus
            />
            <div class="color-picker">
                <span
                    v-for="color in colors"
                    :key="color"
                    class="color-dot"
                    :class="{ 'color-dot--active': newColor === color }"
                    :style="{ background: color }"
                    @click="newColor = color"
                />
            </div>
            <div class="home__form-actions">
                <button class="btn btn--primary" @click="createBoard">
                    Aanmaken
                </button>
                <button class="btn btn--ghost" @click="cancelForm">
                    Annuleren
                </button>
            </div>
        </div>

        <div class="home__grid">
            <div v-for="board in boards" :key="board.id" class="board-card">
                <div
                    class="board-card__top"
                    :style="board.color ? { background: board.color } : {}"
                ></div>
                <div class="board-card__body">
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
                </div>
                <div class="board-card__footer">
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
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { api } from "../api";

const emit = defineEmits(["select"]);

const boards = ref([]);
const showForm = ref(false);
const newTitle = ref("");
const newColor = ref("#6c8ebf");
const editingId = ref(null);
const editTitle = ref("");

const colors = [
    "#6c8ebf",
    "#7db87d",
    "#e0a84a",
    "#c47878",
    "#9b7bc4",
    "#5bb8c4",
    "#c4895b",
    "#7b9ec4",
];

onMounted(async () => {
    boards.value = await api.getBoards();
});

async function createBoard() {
    if (!newTitle.value.trim()) return;
    const board = await api.createBoard({
        title: newTitle.value.trim(),
        color: newColor.value,
    });
    boards.value.unshift(board);
    cancelForm();
}

function cancelForm() {
    showForm.value = false;
    newTitle.value = "";
    newColor.value = "#6c8ebf";
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
