<template>
    <div class="home">
        <div class="home__header">
            <div>
                <h1 class="home__title">Boards</h1>
                <p class="home__subtitle">
                    {{ boards.length }} board{{
                        boards.length !== 1 ? "s" : ""
                    }}
                </p>
            </div>
            <button
                class="btn btn--primary"
                @click="showForm = true"
                v-if="!showForm"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="14"
                    height="14"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Nieuw board
            </button>
        </div>

        <div v-if="showForm" class="home__form">
            <input
                v-model="newTitle"
                class="input"
                placeholder="Board naam"
                autofocus
                @keyup.enter="createBoard"
            />
            <div class="home__form-colors">
                <span class="home__form-label">Kleur</span>
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
            <div
                v-for="board in boards"
                :key="board.id"
                class="board-card"
                @click="$emit('select', board)"
            >
                <div
                    class="board-card__top"
                    :style="board.color ? { background: board.color } : {}"
                ></div>
                <div class="board-card__body">
                    <div
                        v-if="editingId !== board.id"
                        class="board-card__title"
                    >
                        {{ board.title }}
                    </div>
                    <input
                        v-else
                        v-model="editTitle"
                        class="input"
                        @keyup.enter="saveEdit(board)"
                        @keyup.escape="cancelEdit"
                        @click.stop
                        autofocus
                    />
                </div>
                <div class="board-card__footer" @click.stop>
                    <div class="board-card__actions">
                        <button
                            class="board-card__action-btn"
                            @click.stop="startEdit(board)"
                            title="Bewerken"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                                />
                                <path
                                    d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"
                                />
                            </svg>
                        </button>
                        <button
                            class="board-card__action-btn board-card__action-btn--danger"
                            @click.stop="deleteBoard(board)"
                            title="Verwijderen"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <polyline points="3 6 5 6 21 6" />
                                <path
                                    d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                                />
                                <path d="M10 11v6" />
                                <path d="M14 11v6" />
                                <path
                                    d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"
                                />
                            </svg>
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
const newColor = ref("#5b8dee");
const editingId = ref(null);
const editTitle = ref("");

const colors = [
    "#5b8dee",
    "#7db87d",
    "#e0a84a",
    "#c47878",
    "#9b7bc4",
    "#5bb8c4",
    "#c4895b",
    "#e06c9f",
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
    newColor.value = "#5b8dee";
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
