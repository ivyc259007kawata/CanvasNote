<template>
    <div class="viewer-page">

        <!-- ヘッダー -->
        <header class="viewer-header">

            <button class="back-button" @click="$emit('back')">
                ← 教材一覧
            </button>

            <div class="lesson-info">
                <span class="lesson-label">
                    📖 教材閲覧
                </span>

                <h1>
                    {{ lessonTitle }}
                </h1>
            </div>

        </header>


        <!-- ページタブ -->
        <div v-if="pages.length > 0" class="page-tabs">

            <button v-for="(page, index) in pages" :key="page.id" class="page-button" :class="{
                active: currentPage === index
            }" @click="changePage(index)">
                {{ page.title }}
            </button>

        </div>


        <!-- Canvas -->
        <main class="viewer-content">

            <p v-if="loading" class="message">
                教材を読み込んでいます...
            </p>

            <p v-else-if="error" class="message error">
                {{ error }}
            </p>

            <div v-else class="canvas-wrapper">

                <canvas ref="canvasEl"></canvas>

            </div>

        </main>

    </div>
</template>


<script setup>

import {
    ref,
    onMounted,
    onUnmounted,
    nextTick
} from 'vue'

import { Canvas } from 'fabric'


const props = defineProps({
    lesson: {
        type: Object,
        required: true
    }
})


defineEmits([
    'back'
])


const canvasEl = ref(null)

const fabricCanvas = ref(null)

const pages = ref([])

const currentPage = ref(0)

const lessonTitle = ref(
    props.lesson?.title ?? '教材'
)

const loading = ref(true)

const error = ref(null)


/*
 * Canvasデータ取得
 */
const loadLesson = async () => {

    try {

        const response = await fetch(
            `/student-lessons-json/${props.lesson.id}/canvas`,
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            }
        )


        if (!response.ok) {

            if (response.status === 403) {
                throw new Error(
                    'この教材は現在公開されていません。'
                )
            }

            throw new Error(
                '教材の取得に失敗しました。'
            )
        }


        const data =
            await response.json()


        lessonTitle.value =
            data.lesson.title


        pages.value =
            data.pages.map(page => ({
                id: page.id,
                title: `ページ${page.page_number}`,
                canvasData: page.content
            }))


        if (pages.value.length === 0) {

            throw new Error(
                'この教材にはページがありません。'
            )

        }


        currentPage.value = 0


        //await loadCurrentPage()


    } catch (err) {

        console.error(
            '教材読み込みエラー:',
            err
        )

        error.value =
            err.message

    } finally {

        loading.value = false

    }

}


/*
 * Fabric.js Canvas作成
 */
const initCanvas = () => {

    fabricCanvas.value =
        new Canvas(
            canvasEl.value,
            {
                width: 1000,
                height: 650,
                selection: false
            }
        )

}


/*
 * 現在のページをCanvasに表示
 */
const loadCurrentPage = async () => {

    const fc =
        fabricCanvas.value

    if (!fc) return

    const page =
        pages.value[currentPage.value]

    if (!page) return

    console.log(
        '表示するページ:',
        page
    )

    fc.clear()

    if (page.canvasData) {

        console.log(
            'Canvasデータ:',
            page.canvasData
        )

        await fc.loadFromJSON(
            page.canvasData
        )
    }

    // 生徒側では編集できないようにする
    fc.getObjects().forEach(object => {

        object.set({
            selectable: false,
            evented: false
        })

    })

    fc.discardActiveObject()

    fc.requestRenderAll()

    console.log(
        'Canvasオブジェクト数:',
        fc.getObjects().length
    )

    console.log(
        'Canvasサイズ:',
        fc.getWidth(),
        fc.getHeight()
    )



}


/*
 * ページ変更
 */
const changePage = async (index) => {

    currentPage.value = index

    await loadCurrentPage()

}


/*
 * 初期化
 */
onMounted(async () => {

    await loadLesson()

    await nextTick()

    initCanvas()

    await loadCurrentPage()

})

/*
 * 終了時
 */
onUnmounted(() => {

    if (fabricCanvas.value) {

        fabricCanvas.value.dispose()

    }

})

</script>


<style scoped>
.viewer-page {
    min-height: 100vh;
    padding: 20px 30px;
    box-sizing: border-box;
    background: #f5f7fb;
}


/* ヘッダー */

.viewer-header {
    display: flex;
    align-items: center;
    gap: 25px;
    margin-bottom: 20px;
}


.back-button {
    padding: 10px 16px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background: white;
    color: #374151;
    font-size: 14px;
    cursor: pointer;
}


.back-button:hover {
    background: #f3f4f6;
}


.lesson-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
}


.lesson-label {
    font-size: 12px;
    color: #6b7280;
}


.lesson-info h1 {
    margin: 0;
    font-size: 24px;
    color: #1f2937;
}


/* ページタブ */

.page-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 15px;
    overflow-x: auto;
}


.page-button {
    padding: 8px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #374151;
    cursor: pointer;
}


.page-button.active {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
}


/* Canvas */

.viewer-content {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.05);
}


.canvas-wrapper {
    display: flex;
    justify-content: center;
    overflow: auto;
}


canvas {
    border: 1px solid #d1d5db;
    background: white;
}


.message {
    padding: 30px;
    text-align: center;
    color: #666;
}


.message.error {
    color: #dc2626;
}
</style>
