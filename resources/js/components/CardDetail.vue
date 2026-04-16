<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <button class="modal__close" @click="$emit('close')">✕</button>

            <input
                v-model="localTitle"
                class="input input--title"
                @blur="saveTitle"
                @keyup.enter="saveTitle"
            />

            <div class="modal__section">
                <label class="modal__label">Beschrijving</label>
                <textarea
                    v-model="localDescription"
                    class="input input--textarea"
                    placeholder="Voeg een beschrijving toe..."
                    @blur="saveDescription"
                />
            </div>

            <div class="modal__section">
                <label class="modal__label">Checklist</label>

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

                <form @submit.prevent="addItem" class="checklist-item__form">
                    <input
                        v-model="newItemTitle"
                        class="input"
                        placeholder="Nieuw checklist item"
                    />
                    <button class="btn btn--primary" type="submit">+</button>
                </form>
            </div>

            <div class="modal__footer">
                <button class="btn btn--danger" @click="$emit('delete', card)">
                    Kaart verwijderen
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { api } from "../api";

const props = defineProps({ card: Object });
const emit = defineEmits(["close", "updated", "delete"]);

const localTitle = ref(props.card.title);
const localDescription = ref(props.card.description || "");
const localItems = ref([...(props.card.checklist_items || [])]);
const newItemTitle = ref("");

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

async function addItem() {
    if (!newItemTitle.value.trim()) return;
    const item = await api.createChecklistItem({
        card_id: props.card.id,
        title: newItemTitle.value.trim(),
    });
    localItems.value.push(item);
    newItemTitle.value = "";
}

async function toggleItem(item) {
    const updated = await api.updateChecklistItem(item.id, {
        completed: !item.completed,
    });
    item.completed = updated.completed;
}

async function deleteItem(item) {
    await api.deleteChecklistItem(item.id);
    localItems.value = localItems.value.filter((i) => i.id !== item.id);
}
</script>
