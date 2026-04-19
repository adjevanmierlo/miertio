<template>
    <Teleport to="body">
        <div class="panel-overlay" @click.self="$emit('close')"></div>
        <div class="panel">
            <div
                class="panel__header"
                :style="localCoverColor ? { background: localCoverColor } : {}"
            >
                <button class="panel__close" @click="$emit('close')">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="panel__inner">
                <input
                    v-model="localTitle"
                    class="input input--title"
                    @blur="saveTitle"
                    @keyup.enter="saveTitle"
                />

                <div class="panel__section">
                    <label class="panel__label">Labels</label>
                    <div class="label-list">
                        <span
                            v-for="label in localLabels"
                            :key="label.id"
                            class="label-tag"
                            :style="{ background: label.color }"
                        >
                            {{ label.name }}
                            <button
                                class="label-tag__remove"
                                @click="detachLabel(label)"
                            >
                                ✕
                            </button>
                        </span>
                    </div>
                    <div class="label-picker">
                        <div
                            v-for="label in availableLabels"
                            :key="label.id"
                            class="label-picker__item"
                            :style="{ background: label.color }"
                            :class="{
                                'label-picker__item--active': isAttached(label),
                            }"
                            @click="toggleLabel(label)"
                        >
                            {{ label.name }}
                        </div>
                    </div>
                    <div class="label-create">
                        <input
                            v-model="newLabelName"
                            class="input input--sm"
                            placeholder="Nieuw label"
                        />
                        <div class="label-create__colors">
                            <span
                                v-for="color in labelColors"
                                :key="color"
                                class="color-dot"
                                :class="{
                                    'color-dot--active':
                                        newLabelColor === color,
                                }"
                                :style="{ background: color }"
                                @click="newLabelColor = color"
                            />
                        </div>
                        <button
                            class="btn btn--primary btn--sm"
                            @click="createLabel"
                        >
                            + Label
                        </button>
                    </div>
                </div>

                <div class="panel__section panel__section--row">
                    <div class="panel__section-half">
                        <label class="panel__label">Due date</label>
                        <input
                            type="date"
                            v-model="localDueDate"
                            class="input"
                            @change="saveDueDate"
                        />
                    </div>
                    <div class="panel__section-half">
                        <label class="panel__label">Cover kleur</label>
                        <div class="label-create__colors">
                            <span
                                v-for="color in coverColors"
                                :key="color"
                                class="color-dot"
                                :class="{
                                    'color-dot--active':
                                        localCoverColor === color,
                                }"
                                :style="{ background: color }"
                                @click="setCoverColor(color)"
                            />
                            <span
                                class="color-dot color-dot--none"
                                :class="{
                                    'color-dot--active': !localCoverColor,
                                }"
                                @click="setCoverColor(null)"
                                >✕</span
                            >
                        </div>
                    </div>
                </div>

                <div class="panel__section">
                    <label class="panel__label">Beschrijving</label>
                    <textarea
                        v-model="localDescription"
                        class="input input--textarea"
                        placeholder="Voeg een beschrijving toe..."
                        @blur="saveDescription"
                    />
                </div>

                <div class="panel__section">
                    <label class="panel__label">
                        Checklist
                        <span
                            v-if="localItems.length"
                            class="panel__label-count"
                            >{{
                                localItems.filter((i) => i.completed).length
                            }}/{{ localItems.length }}</span
                        >
                    </label>

                    <div
                        class="checklist-progress"
                        v-if="localItems.length > 0"
                    >
                        <div class="checklist-progress__bar">
                            <div
                                class="checklist-progress__fill"
                                :style="{ width: checklistProgress + '%' }"
                            ></div>
                        </div>
                        <span class="checklist-progress__label"
                            >{{ checklistProgress }}%</span
                        >
                    </div>

                    <div
                        v-for="item in localItems"
                        :key="item.id"
                        class="checklist-item"
                    >
                        <input
                            type="checkbox"
                            :checked="item.completed"
                            @change="toggleItem(item)"
                        />
                        <span
                            class="checklist-item__title"
                            :class="{
                                'checklist-item__title--done': item.completed,
                            }"
                            >{{ item.title }}</span
                        >
                        <button
                            class="btn btn--ghost btn--sm"
                            @click="deleteItem(item)"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="checklist-item__form">
                        <input
                            v-model="newItemTitle"
                            class="input"
                            placeholder="Nieuw checklist item"
                            @keyup.enter="addItem"
                        />
                        <button
                            class="btn btn--primary"
                            type="button"
                            @click="addItem"
                        >
                            +
                        </button>
                    </div>
                </div>

                <div class="panel__footer">
                    <button
                        class="btn btn--danger btn--sm"
                        @click="$emit('delete', card)"
                    >
                        Kaart verwijderen
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { api } from "../api";

const props = defineProps({ card: Object });
const emit = defineEmits(["close", "updated", "delete"]);

const localTitle = ref(props.card.title);
const localDescription = ref(props.card.description || "");
const localDueDate = ref(props.card.due_date || "");
const localCoverColor = ref(props.card.cover_color || null);
const localItems = ref([...(props.card.checklist_items || [])]);
const localLabels = ref([...(props.card.labels || [])]);
const availableLabels = ref([]);
const newItemTitle = ref("");
const newLabelName = ref("");
const newLabelColor = ref("#6c8ebf");

const labelColors = [
    "#6c8ebf",
    "#7db87d",
    "#e0a84a",
    "#c47878",
    "#9b7bc4",
    "#5bb8c4",
    "#c4895b",
    "#7b9ec4",
];

const coverColors = [
    "#6c8ebf",
    "#7db87d",
    "#e0a84a",
    "#c47878",
    "#9b7bc4",
    "#5bb8c4",
];

const checklistProgress = computed(() => {
    if (!localItems.value.length) return 0;
    const done = localItems.value.filter((i) => i.completed).length;
    return Math.round((done / localItems.value.length) * 100);
});

onMounted(async () => {
    availableLabels.value = await api.getLabels(props.card.board_id);
});

function isAttached(label) {
    return localLabels.value.some((l) => l.id === label.id);
}

async function toggleLabel(label) {
    if (isAttached(label)) {
        await api.detachLabel(props.card.id, label.id);
        localLabels.value = localLabels.value.filter((l) => l.id !== label.id);
    } else {
        await api.attachLabel(props.card.id, label.id);
        localLabels.value.push(label);
    }
    emit("updated", { ...props.card, labels: localLabels.value });
}

async function detachLabel(label) {
    await api.detachLabel(props.card.id, label.id);
    localLabels.value = localLabels.value.filter((l) => l.id !== label.id);
    emit("updated", { ...props.card, labels: localLabels.value });
}

async function createLabel() {
    if (!newLabelName.value.trim()) return;
    const label = await api.createLabel({
        board_id: props.card.board_id,
        name: newLabelName.value.trim(),
        color: newLabelColor.value,
    });
    availableLabels.value.push(label);
    newLabelName.value = "";
}

async function saveTitle() {
    if (!localTitle.value.trim()) return;
    if (localTitle.value === props.card.title) return;
    const updated = await api.updateCard(props.card.id, {
        title: localTitle.value.trim(),
    });
    emit("updated", updated);
}

async function saveDescription() {
    if (localDescription.value === (props.card.description || "")) return;
    const updated = await api.updateCard(props.card.id, {
        description: localDescription.value,
    });
    emit("updated", updated);
}

async function saveDueDate() {
    const updated = await api.updateCard(props.card.id, {
        due_date: localDueDate.value,
    });
    emit("updated", {
        ...props.card,
        due_date: localDueDate.value,
        ...updated,
    });
}

async function setCoverColor(color) {
    localCoverColor.value = color;
    const updated = await api.updateCard(props.card.id, { cover_color: color });
    emit("updated", { ...props.card, cover_color: color, ...updated });
}

async function addItem() {
    if (!newItemTitle.value.trim()) return;
    const item = await api.createChecklistItem({
        card_id: props.card.id,
        title: newItemTitle.value.trim(),
    });
    localItems.value.push(item);
    newItemTitle.value = "";
    emit("updated", { ...props.card, checklist_items: localItems.value });
}

async function toggleItem(item) {
    const updated = await api.updateChecklistItem(item.id, {
        completed: !item.completed,
    });
    item.completed = updated.completed;
    emit("updated", { ...props.card, checklist_items: localItems.value });
}

async function deleteItem(item) {
    await api.deleteChecklistItem(item.id);
    localItems.value = localItems.value.filter((i) => i.id !== item.id);
    emit("updated", { ...props.card, checklist_items: localItems.value });
}
</script>
