<template>
    <div class="board-view">
        <div class="board-view__header">
            <button class="btn btn--ghost" @click="$emit('back')">
                ← Terug
            </button>
            <h2 class="board-view__title">{{ board.title }}</h2>
        </div>

        <div class="board-view__columns">
            <div v-for="column in columns" :key="column.id" class="column">
                <div class="column__header">
                    <span
                        v-if="editingColumnId !== column.id"
                        class="column__title"
                        >{{ column.title }}</span
                    >
                    <input
                        v-else
                        v-model="editColumnTitle"
                        class="input input--sm"
                        @keyup.enter="saveColumnEdit(column)"
                        @keyup.escape="cancelColumnEdit"
                        autofocus
                    />
                    <div class="column__actions">
                        <button
                            class="btn btn--ghost btn--sm"
                            @click="startColumnEdit(column)"
                        >
                            ✏️
                        </button>
                        <button
                            class="btn btn--ghost btn--sm"
                            @click="deleteColumn(column)"
                        >
                            🗑️
                        </button>
                    </div>
                </div>

                <div class="column__cards">
                    <div
                        v-for="card in column.cards"
                        :key="card.id"
                        class="card-item"
                        @click="openCard(card)"
                    >
                        {{ card.title }}
                    </div>
                </div>

                <button
                    class="btn btn--ghost btn--block"
                    @click="startAddCard(column)"
                >
                    + Kaart
                </button>

                <form
                    v-if="addingCardColumnId === column.id"
                    @submit.prevent="createCard(column)"
                >
                    <input
                        v-model="newCardTitle"
                        class="input"
                        placeholder="Kaart titel"
                        autofocus
                    />
                    <div class="board-list__form-actions">
                        <button class="btn btn--primary" type="submit">
                            Toevoegen
                        </button>
                        <button
                            class="btn btn--ghost"
                            type="button"
                            @click="cancelAddCard"
                        >
                            Annuleren
                        </button>
                    </div>
                </form>
            </div>

            <div class="column column--add">
                <form v-if="showColumnForm" @submit.prevent="createColumn">
                    <input
                        v-model="newColumnTitle"
                        class="input"
                        placeholder="Column naam"
                        autofocus
                    />
                    <div class="board-list__form-actions">
                        <button class="btn btn--primary" type="submit">
                            Toevoegen
                        </button>
                        <button
                            class="btn btn--ghost"
                            type="button"
                            @click="showColumnForm = false"
                        >
                            Annuleren
                        </button>
                    </div>
                </form>
                <button
                    v-else
                    class="btn btn--ghost btn--block"
                    @click="showColumnForm = true"
                >
                    + Column
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { api } from "../api";

const props = defineProps({ board: Object });
const emit = defineEmits(["back"]);

const columns = ref([]);
const showColumnForm = ref(false);
const newColumnTitle = ref("");
const editingColumnId = ref(null);
const editColumnTitle = ref("");
const addingCardColumnId = ref(null);
const newCardTitle = ref("");

onMounted(async () => {
    const data = await api.getBoard(props.board.id);
    columns.value = data.columns;
});

async function createColumn() {
    if (!newColumnTitle.value.trim()) return;
    const column = await api.createColumn({
        board_id: props.board.id,
        title: newColumnTitle.value.trim(),
    });
    column.cards = [];
    columns.value.push(column);
    newColumnTitle.value = "";
    showColumnForm.value = false;
}

function startColumnEdit(column) {
    editingColumnId.value = column.id;
    editColumnTitle.value = column.title;
}

async function saveColumnEdit(column) {
    if (!editColumnTitle.value.trim()) return;
    const updated = await api.updateColumn(column.id, {
        title: editColumnTitle.value.trim(),
    });
    column.title = updated.title;
    cancelColumnEdit();
}

function cancelColumnEdit() {
    editingColumnId.value = null;
    editColumnTitle.value = "";
}

async function deleteColumn(column) {
    if (!confirm(`Column "${column.title}" verwijderen?`)) return;
    await api.deleteColumn(column.id);
    columns.value = columns.value.filter((c) => c.id !== column.id);
}

function startAddCard(column) {
    addingCardColumnId.value = column.id;
    newCardTitle.value = "";
}

async function createCard(column) {
    if (!newCardTitle.value.trim()) return;
    const card = await api.createCard({
        column_id: column.id,
        board_id: props.board.id,
        title: newCardTitle.value.trim(),
    });
    card.checklist_items = [];
    column.cards.push(card);
    cancelAddCard();
}

function cancelAddCard() {
    addingCardColumnId.value = null;
    newCardTitle.value = "";
}

function openCard(card) {
    // volgende stap
}
</script>
