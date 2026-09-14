<template>
    <div class="viewer-page">

        <!-- ヘッダー -->
        <header class="viewer-header">

            <button class="back-button" @click="$emit('back')">
                ← 教材一覧
            </button>

            <div class="lesson-info">
                <span class="lesson-label">
                    📖 {{ isAnswerMode ? '回答中' : '教材閲覧' }}
                </span>

                <h1>
                    {{ lessonTitle }}
                </h1>
            </div>

            <button v-if="!isAnswerMode" class="answer-button" @click="startAnswer">
                ✏️ 回答する
            </button>

            <template v-else>
                <button class="save-button" @click="saveAnswer">
                    💾 回答を保存
                </button>

                <button class="submit-button" @click="submitAnswer">
                    📤 提出する
                </button>

                <button class="cancel-button" @click="cancelAnswer">
                    閲覧に戻る
                </button>
            </template>

        </header>

        <!-- 回答用ツールバー -->
        <div v-if="isAnswerMode" class="answer-toolbar">
            <button @click="setTool('pen')">✏️ ペン</button>
            <button @click="setTool('marker')">🖍 マーカー</button>
            <button @click="setTool('rectangle')">▭ 四角</button>
            <button @click="setTool('text')">📝 テキスト</button>
            <button @click="setTool('eraser')">🧽 消しゴム</button>
            <button @click="setTool('select')">↖ 選択</button>
        </div>


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

import {
    Canvas,
    Rect,
    IText,
    PencilBrush
} from 'fabric'


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

const isAnswerMode = ref(false)

const answerTool = ref('select')


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

    fabricCanvas.value.on(
        'mouse:down',
        handleCanvasClick
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

    const canvasData = isAnswerMode.value
        ? (page.answerData ?? page.canvasData)
        : page.canvasData

    if (canvasData) {
        console.log('Canvasデータ:', canvasData)
        await fc.loadFromJSON(canvasData)
    }

    // 閲覧モードでは編集できないようにする
    if (!isAnswerMode.value) {
        fc.getObjects().forEach(object => {
            object.set({
                selectable: false,
                evented: false
            })
        })
    }

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

const saveCurrentAnswerPage = () => {
    if (!isAnswerMode.value) return

    const fc = fabricCanvas.value
    if (!fc) return

    const page = pages.value[currentPage.value]
    if (!page) return

    page.answerData = fc.toJSON()
}

const changePage = async (index) => {
    saveCurrentAnswerPage()

    currentPage.value = index

    await loadCurrentPage()
}

const saveAnswer = async () => {
    // 今いるページの最新状態を保存
    saveCurrentAnswerPage()

    try {
        const pagesData = pages.value.map((page, index) => ({
            page_number: index + 1,
            content: page.answerData ?? page.canvasData
        }))

        const response = await fetch(
            `/student-lessons-json/${props.lesson.id}/submission`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                },
                body: JSON.stringify({
                    pages: pagesData
                })
            }
        )

        if (!response.ok) {
            throw new Error('回答の保存に失敗しました。')
        }

        const data = await response.json()

        console.log('回答保存成功:', data)

        alert('回答を保存しました。')

    } catch (err) {
        console.error('回答保存エラー:', err)

        alert(err.message)
    }
}

const submitAnswer = async () => {
    try {
        const response = await fetch(
            `/student-lessons-json/${props.lesson.id}/submission/submit`,
            {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content')
                }
            }
        )

        if (!response.ok) {
            const data = await response.json()
            throw new Error(
                data.message || '回答の提出に失敗しました。'
            )
        }

        const data = await response.json()

        console.log('回答提出成功:', data)

        alert('回答を提出しました。')

        // 回答モードを終了
        isAnswerMode.value = false

        // 閲覧画面に戻す
        await loadCurrentPage()

    } catch (err) {
        console.error('回答提出エラー:', err)

        alert(err.message)
    }
}

/*
 * 生徒回答
 */
const startAnswer = () => {
    isAnswerMode.value = true
    answerTool.value = 'select'

    if (fabricCanvas.value) {
        fabricCanvas.value.isDrawingMode = false

        fabricCanvas.value.getObjects().forEach(object => {
            object.set({
                selectable: false,
                evented: false
            })
        })

        fabricCanvas.value.requestRenderAll()
    }
}

const cancelAnswer = () => {
    isAnswerMode.value = false

    if (fabricCanvas.value) {
        fabricCanvas.value.isDrawingMode = false

        fabricCanvas.value.getObjects().forEach(object => {
            object.set({
                selectable: false,
                evented: false
            })
        })

        fabricCanvas.value.discardActiveObject()
        fabricCanvas.value.requestRenderAll()
    }
}

const setTool = (tool) => {
    if (!fabricCanvas.value) return

    answerTool.value = tool

    const fc = fabricCanvas.value

    fc.isDrawingMode = false

    // ペン
    if (tool === 'pen') {
        if (!fc.freeDrawingBrush) {
            fc.freeDrawingBrush = new PencilBrush(fc)
        }

        fc.freeDrawingBrush.color = '#000000'
        fc.freeDrawingBrush.width = 5
        fc.isDrawingMode = true
        return
    }

    // マーカー
    if (tool === 'marker') {
        if (!fc.freeDrawingBrush) {
            fc.freeDrawingBrush = new PencilBrush(fc)
        }

        fc.freeDrawingBrush.color =
            'rgba(255,255,0,0.4)'

        fc.freeDrawingBrush.width = 25
        fc.isDrawingMode = true
        return
    }

    // その他
    fc.requestRenderAll()
}

const handleCanvasClick = (event) => {
    if (!isAnswerMode.value) return

    const fc = fabricCanvas.value
    if (!fc) return

    const pointer = fc.getScenePoint(event.e)

    // 四角
    if (answerTool.value === 'rectangle') {
        const rect = new Rect({
            left: pointer.x,
            top: pointer.y,
            width: 120,
            height: 120,
            fill: '#dbeafe',
            stroke: '#2563eb',
            strokeWidth: 2
        })

        fc.add(rect)
        fc.requestRenderAll()

        answerTool.value = 'select'
        return
    }

    // テキスト
    if (answerTool.value === 'text') {
        const text = new IText('回答', {
            left: pointer.x,
            top: pointer.y,
            fontSize: 30,
            fill: '#000000'
        })

        fc.add(text)
        fc.setActiveObject(text)
        text.enterEditing()
        text.selectAll()

        fc.requestRenderAll()

        answerTool.value = 'select'
    }
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

.answer-toolbar {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 15px;
    padding: 10px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.answer-toolbar button {
    padding: 8px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    cursor: pointer;
}

.answer-toolbar button:hover {
    background: #f3f4f6;
}
</style>
