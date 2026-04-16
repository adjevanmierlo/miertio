<template>
    <div id="kanban-app">
        <BoardList v-if="!activeBoard" @select="openBoard" />
        <BoardView v-else :board="activeBoard" @back="activeBoard = null" />
    </div>
</template>

<script setup>
import { ref } from "vue";
import BoardList from "./components/BoardList.vue";
import BoardView from "./components/BoardView.vue";

const activeBoard = ref(null);

function openBoard(board) {
    activeBoard.value = board;
}
</script>
