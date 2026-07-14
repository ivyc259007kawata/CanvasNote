<template>

    <div class="editor-page">

        <button @click="$emit('back')">
            ← 教材一覧へ戻る
        </button>

        <h2>{{ lesson?.title }}</h2>

        <CanvasNote :lesson="lesson" />

    </div>

</template>

<script setup>

import CanvasNote from '@/canvas/CanvasNote.vue'

defineProps({
    lesson: Object
})

defineEmits([
    'back'
])

</script>

<style scoped>

.editor-page{
    padding:20px;
}

h2{
    margin:20px 0;
}

</style>