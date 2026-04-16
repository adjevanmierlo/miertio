<template>
    <div class="board-view">
        <CardDetail
            v-if="activeCard"
            :card="activeCard"
            @close="activeCard = null"
            @updated="onCardUpdated"
            @delete="onCardDelete"
        />

        <div class="board-view__header">
            <button class="btn btn--ghost" @click="$emit('back')">
                ← Terug
            </button>
            <h2 class="board-view__title">{{ board.title }}</h2>
        </div>

        <div class="board-view__columns">
            <draggable
                v-model="columns"
                item-key="id"
                handle=".column__title"
                @end="onColumnReorder"
                class="board-view__columns-inner"
            >
                <template #item="{ element: column }">
                    <div class="column">
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

                        <draggable
                            v-model="column.cards"
                            item-key="id"
                            group="cards"
                            @end="onCardReorder"
                            class="column__cards"
                        >
                            <template #item="{ element: card }">
                                <div class="card-item" @click="openCard(card)">
                                    {{ card.title }}
                                </div>
                            </template>
                        </draggable>

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
                </template>
            </draggable>

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
import CardDetail from "./CardDetail.vue";
import draggable from "vuedraggable";

const props = defineProps({ board: Object });
const emit = defineEmits(["back"]);

const columns = ref([]);
const showColumnForm = ref(false);
const newColumnTitle = ref("");
const editingColumnId = ref(null);
const editColumnTitle = ref("");
const addingCardColumnId = ref(null);
const newCardTitle = ref("");
const activeCard = ref(null);

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
    activeCard.value = card;
}

function onCardUpdated(updated) {
    for (const column of columns.value) {
        const index = column.cards.findIndex((c) => c.id === updated.id);
        if (index !== -1) {
            column.cards[index] = { ...column.cards[index], ...updated };
            break;
        }
    }
}

async function onCardDelete(card) {
    if (!confirm(`Kaart "${card.title}" verwijderen?`)) return;
    await api.deleteCard(card.id);
    for (const column of columns.value) {
        column.cards = column.cards.filter((c) => c.id !== card.id);
    }
    activeCard.value = null;
}

async function onColumnReorder() {
    await api.reorderColumns({
        columns: columns.value.map((c) => c.id),
    });
}

async function onCardReorder(event) {
    const allColumns = columns.value;
    for (const column of allColumns) {
        await api.reorderCards({
            cards: column.cards.map((c) => c.id),
        });
    }

    if (event.from !== event.to) {
        const cardEl = event.item;
        const cardId = parseInt(cardEl.dataset.id);
        const toColumn = allColumns.find((c) =>
            c.cards.some((card) => card.id === cardId),
        );
        if (toColumn) {
            const card = toColumn.cards.find((c) => c.id === cardId);
            if (card) {
                await api.moveCard(card.id, {
                    column_id: toColumn.id,
                    position: toColumn.cards.indexOf(card),
                });
                card.column_id = toColumn.id;
            }
        }
    }
}
</script>
