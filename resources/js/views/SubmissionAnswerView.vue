<template>
    <div class="submission-answer-page">

        <h1>
            📖 提出回答
        </h1>

        <h2>
            👤 {{ submission?.student?.name }}
        </h2>

        <div class="canvas-area">
            <canvas ref="canvasElement" width="1000" height="650"></canvas>
        </div>

        <div class="page-navigation" v-if="totalPages > 0">
            <button @click="previousPage" :disabled="currentPage === 1">
                ← 前のページ
            </button>

            <span>
                {{ currentPage }} / {{ totalPages }}
            </span>

            <button @click="nextPage" :disabled="currentPage === totalPages">
                次のページ →
            </button>
        </div>

        <button @click="$emit('back')">
            ← 提出状況に戻る
        </button>

    </div>
</template>

<script setup>

import { ref, computed, onMounted } from 'vue'
import { Canvas } from 'fabric'

const props = defineProps({
    submission: {
        type: Object,
        required: true
    }
})

defineEmits([
    'back'
])

const canvasElement = ref(null)

let canvas = null

// 現在表示しているページ
const currentPage = ref(1)

// 全ページ数
const totalPages = computed(() => {
    return props.submission?.elements?.length ?? 0
})

const loadPage = async (pageNumber) => {

    const page =
        props.submission.elements?.find(
            element =>
                element.page_number === pageNumber
        )

    if (!page) {
        console.log(
            `${pageNumber}ページ目の回答がありません`
        )
        return
    }

    // 現在のCanvasを一度空にする
    canvas.clear()

    // 保存されていたページを復元
    await canvas.loadFromJSON(
        page.content
    )

    // 先生が回答を編集できないようにする
    canvas.selection = false

    canvas.forEachObject(object => {
        object.selectable = false
        object.evented = false
    })

    canvas.renderAll()

    // 現在ページを更新
    currentPage.value = pageNumber

    console.log(
        `${pageNumber}ページ目を表示しました`
    )
}

onMounted(async () => {

    // 提出データを確認
    console.log(
        '回答表示画面:',
        props.submission
    )

    // 回答ページを確認
    console.log(
        '回答ページ:',
        props.submission.elements
    )

    // Fabric.js のCanvasを作成
    canvas = new Canvas(
        canvasElement.value,
        {
            width: 1000,
            height: 650,
            selection: false
        }
    )

    // 最初は1ページ目を表示
    await loadPage(1)
})

const previousPage = () => {

    if (currentPage.value <= 1) {
        return
    }

    loadPage(
        currentPage.value - 1
    )
}

const nextPage = () => {

    if (
        currentPage.value >=
        totalPages.value
    ) {
        return
    }

    loadPage(
        currentPage.value + 1
    )
}

</script>

<style scoped>
.submission-answer-page {
    padding: 30px;
}

button {
    margin-top: 20px;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    background: #3b82f6;
    color: white;
    cursor: pointer;
}

.canvas-area {
    margin-top: 20px;
    padding: 20px;
    background: #f3f4f6;
    border-radius: 12px;
    overflow: auto;
}

.canvas-area canvas {
    display: block;
    background: white;
    border: 1px solid #ddd;
}

.page-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

.page-navigation button {
    margin-top: 0;
}

.page-navigation button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.page-navigation span {
    font-weight: bold;
    min-width: 60px;
    text-align: center;
}
</style>