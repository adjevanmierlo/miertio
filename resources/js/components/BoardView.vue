<template>
    <div
        class="board-view"
        :style="
            board.color ? { backgroundColor: adjustColor(board.color) } : {}
        "
    >
        <CardDetail
            v-if="activeCard"
            :card="activeCard"
            @close="activeCard = null"
            @updated="onCardUpdated"
            @delete="onCardDelete"
        />

        <div class="board-view__columns-wrap">
            <div class="board-view__columns-inner">
                <draggable
                    v-model="columns"
                    item-key="id"
                    handle=".column__title"
                    @end="onColumnReorder"
                    style="
                        display: flex;
                        gap: 16px;
                        align-items: flex-start;
                        flex-shrink: 0;
                    "
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
                                    <span class="column__count">{{
                                        column.cards.length
                                    }}</span>
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

                            <div class="column__body">
                                <draggable
                                    v-model="column.cards"
                                    item-key="id"
                                    group="cards"
                                    @end="onCardReorder"
                                    class="column__cards"
                                >
                                    <template #item="{ element: card }">
                                        <div
                                            class="card-item"
                                            @click="openCard(card)"
                                        >
                                            <div
                                                class="card-item__priority"
                                                :style="{
                                                    background:
                                                        getPriorityColor(
                                                            card.priority,
                                                        ),
                                                }"
                                            ></div>
                                            <div
                                                v-if="card.cover_color"
                                                class="card-item__cover"
                                                :style="{
                                                    background:
                                                        card.cover_color,
                                                }"
                                            ></div>
                                            <div class="card-item__body">
                                                <div
                                                    v-if="
                                                        card.labels &&
                                                        card.labels.length
                                                    "
                                                    class="card-item__labels"
                                                >
                                                    <span
                                                        v-for="label in card.labels"
                                                        :key="label.id"
                                                        class="card-item__label"
                                                        :style="{
                                                            background:
                                                                label.color,
                                                        }"
                                                        >{{ label.name }}</span
                                                    >
                                                </div>
                                                <div class="card-item__title">
                                                    {{ card.title }}
                                                </div>
                                                <div
                                                    v-if="
                                                        card.due_date ||
                                                        (card.checklist_items &&
                                                            card.checklist_items
                                                                .length)
                                                    "
                                                    class="card-item__meta"
                                                >
                                                    <span
                                                        v-if="card.due_date"
                                                        class="card-item__due"
                                                        :class="{
                                                            'card-item__due--overdue':
                                                                isOverdue(
                                                                    card.due_date,
                                                                ),
                                                        }"
                                                        >📅
                                                        {{
                                                            formatDate(
                                                                card.due_date,
                                                            )
                                                        }}</span
                                                    >
                                                    <span
                                                        v-if="
                                                            card.checklist_items &&
                                                            card.checklist_items
                                                                .length
                                                        "
                                                        class="card-item__checklist"
                                                    >
                                                        ✓
                                                        {{
                                                            card.checklist_items.filter(
                                                                (i) =>
                                                                    i.completed,
                                                            ).length
                                                        }}/{{
                                                            card.checklist_items
                                                                .length
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </draggable>
                            </div>

                            <div class="column__footer">
                                <div
                                    v-if="addingCardColumnId === column.id"
                                    class="column__add-form"
                                >
                                    <textarea
                                        v-model="newCardTitle"
                                        class="input input--textarea input--card-add"
                                        placeholder="Kaarttitel..."
                                        rows="2"
                                        autofocus
                                        @keyup.enter.prevent="
                                            createCardAndContinue(column)
                                        "
                                        @keyup.escape="cancelAddCard"
                                    />
                                    <div class="column__add-actions">
                                        <button
                                            class="btn btn--primary btn--sm"
                                            @click="createCard(column)"
                                        >
                                            Kaart toevoegen
                                        </button>
                                        <button
                                            class="btn btn--ghost btn--sm"
                                            @click="cancelAddCard"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>
                                <button
                                    v-else
                                    class="btn btn--ghost btn--block column__add-btn"
                                    @click="startAddCard(column)"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="13"
                                        height="13"
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
                                    Kaart toevoegen
                                </button>
                            </div>
                        </div>
                    </template>
                </draggable>

                <div class="column column--add">
                    <button
                        v-if="!showColumnForm"
                        class="btn btn--ghost"
                        @click="showColumnForm = true"
                    >
                        + Column
                    </button>
                    <div
                        v-else
                        style="
                            width: 100%;
                            display: flex;
                            flex-direction: column;
                            gap: 8px;
                        "
                    >
                        <input
                            v-model="newColumnTitle"
                            class="input"
                            placeholder="Column naam"
                            autofocus
                        />
                        <div style="display: flex; gap: 6px">
                            <button
                                class="btn btn--primary btn--sm"
                                @click="createColumn"
                            >
                                Toevoegen
                            </button>
                            <button
                                class="btn btn--ghost btn--sm"
                                @click="showColumnForm = false"
                            >
                                Annuleren
                            </button>
                        </div>
                    </div>
                </div>
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

// Prioriteit kleur functie
function getPriorityColor(priority) {
    const colors = {
        low: "#10b981",
        normal: "#6b9fff",
        high: "#f59e0b",
        urgent: "#ef4444",
    };
    return colors[priority] || colors.normal;
}

onMounted(async () => {
    const data = await api.getBoard(props.board.id);
    columns.value = data.columns;
});

function adjustColor(hex) {
    return hex + "18";
}

function formatDate(date) {
    if (!date) return "";
    return new Date(date).toLocaleDateString("nl-NL", {
        day: "numeric",
        month: "short",
    });
}

function isOverdue(date) {
    if (!date) return false;
    return new Date(date) < new Date();
}

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
    card.labels = [];
    column.cards.push(card);
    cancelAddCard();
}

function cancelAddCard() {
    addingCardColumnId.value = null;
    newCardTitle.value = "";
}

async function openCard(card) {
    activeCard.value = card;
}

function onCardUpdated(updated) {
    for (const column of columns.value) {
        const index = column.cards.findIndex((c) => c.id === updated.id);
        if (index !== -1) {
            column.cards[index] = { ...column.cards[index], ...updated };
            if (activeCard.value && activeCard.value.id === updated.id) {
                activeCard.value = { ...activeCard.value, ...updated };
            }
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
