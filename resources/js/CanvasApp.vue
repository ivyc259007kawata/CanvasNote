<template>

    <DashboardView
        v-if="page==='dashboard'"
        @edit="openEditor"
    />

    <CanvasEditorView
        v-else
        :lesson="lesson"
        @back="backDashboard"
    />

</template>

<script setup>

import { ref } from 'vue'

import DashboardView from './views/DashboardView.vue'
import CanvasEditorView from './views/CanvasEditorView.vue'

const page = ref('dashboard')

const lesson = ref(null)

function openEditor(target){

    lesson.value = target

    page.value = 'editor'

}

function backDashboard(){

    page.value = 'dashboard'

}

</script>

<style scoped>
.app {

    display: flex;
    flex-direction: column;

}

header {

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 20px;

    background: #2563eb;

    color: white;

}

nav {

    display: flex;
    gap: 10px;

}

button {

    padding: 8px 14px;

    border: none;

    border-radius: 8px;

    cursor: pointer;

}

main {

    padding: 20px;

}
</style>