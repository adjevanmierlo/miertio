<template>
    <div id="kanban-app">
        <header class="app-header">
            <div class="app-header__left">
                <template v-if="activeBoard">
                    <button class="btn btn--ghost btn--sm" @click="closeBoard">
                        ← Terug
                    </button>
                    <div class="app-header__divider"></div>
                    <div
                        v-if="activeBoard.color"
                        class="app-header__accent"
                        :style="{ background: activeBoard.color }"
                    ></div>
                    <span class="app-header__board-title">{{
                        activeBoard.title
                    }}</span>
                </template>
                <template v-else>
                    <span class="app-header__logo">
                        <img
                            :src="faviconUrl"
                            alt=""
                            class="app-header__favicon"
                        />
                        Miertio
                    </span>
                </template>
            </div>
            <div class="app-header__right">
                <button
                    class="theme-toggle"
                    @click="toggleTheme"
                    :title="isDark ? 'Licht' : 'Donker'"
                >
                    <svg
                        v-if="isDark"
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="5" />
                        <line x1="12" y1="1" x2="12" y2="3" />
                        <line x1="12" y1="21" x2="12" y2="23" />
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                        <line x1="1" y1="12" x2="3" y2="12" />
                        <line x1="21" y1="12" x2="23" y2="12" />
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                    </svg>
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path
                            d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"
                        />
                    </svg>
                </button>
            </div>
        </header>

        <BoardList v-if="!activeBoard" @select="openBoard" />
        <BoardView
            v-else
            :board="activeBoard"
            @back="closeBoard"
            @updated="onBoardUpdated"
        />
    </div>
</template>

<script setup>
import { ref } from "vue";
import BoardList from "./components/BoardList.vue";
import BoardView from "./components/BoardView.vue";

const activeBoard = ref(null);
const isDark = ref(false);
const faviconUrl = window.location.origin + "/images/favicon.ico";

function openBoard(board) {
    activeBoard.value = board;
    document.title = `${board.title} — Miertio`;
}

function closeBoard() {
    activeBoard.value = null;
    document.title = "Miertio";
}

function onBoardUpdated(board) {
    activeBoard.value = board;
    document.title = `${board.title} — Miertio`;
}

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.setAttribute(
        "data-theme",
        isDark.value ? "dark" : "",
    );
}
</script>
