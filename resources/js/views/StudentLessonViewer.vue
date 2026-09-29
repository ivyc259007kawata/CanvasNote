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

            <template v-if="!isAnswerMode">

                <button v-if="submissionStatus === 'submitted'" class="withdraw-button" @click="withdrawSubmission">
                    ↩️ 提出を取り消す
                </button>

                <div v-else-if="submissionStatus === 'returned'" class="returned-area">
                    <span class="returned-label">
                        🟣 採点結果が返却されています
                    </span>

                    <button class="edit-returned-button" @click="startReturnedAnswer">
                        ✏️ 修正して再提出
                    </button>
                </div>

                <button v-else class="answer-button" @click="startAnswer">
                    ✏️ 回答する
                </button>

            </template>

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

        <!-- 採点結果 -->
        <section v-if="submissionStatus === 'returned'" class="grading-result">
            <h2>📝 採点結果</h2>

            <div class="score">
                <span>点数</span>
                <strong>
                    {{ submissionScore }} / 100
                </strong>
            </div>

            <div v-if="submissionComment" class="comment">
                <h3>💬 先生からのコメント</h3>

                <p>
                    {{ submissionComment }}
                </p>
            </div>
        </section>

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
    PencilBrush,
    FabricObject
} from 'fabric'

FabricObject.customProperties = ['cnRole']

const props = defineProps({
    lesson: {
        type: Object,
        required: true
    }
})

defineEmits(['back'])


const canvasEl = ref(null)
const fabricCanvas = ref(null)
const pages = ref([])
const currentPage = ref(0)
const lessonTitle = ref(props.lesson?.title ?? '教材')
const loading = ref(true)
const error = ref(null)
const isAnswerMode = ref(false)
const answerTool = ref('select')
const submissionStatus = ref(null)
const submissionScore = ref(null)
const submissionComment = ref('')


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


        // 保存済みの生徒回答を取得
        const answerResponse = await fetch(
            `/student-lessons-json/${props.lesson.id}/submission`,
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!answerResponse.ok) {
            throw new Error('保存済み回答の取得に失敗しました。')
        }

        const answerData = await answerResponse.json()
        submissionStatus.value = answerData.submission?.status ?? null
        //点数
        submissionScore.value =
            answerData.submission?.score ?? null
        //先生側コメント
        submissionComment.value =
            answerData.submission?.comment ?? ''
        // 保存済み回答があれば、該当ページに設定
        if (answerData.elements) {
            answerData.elements.forEach(element => {
                const page = pages.value.find(
                    page => page.title === `ページ${element.page_number}`
                )

                if (page) {
                    page.answerData = element.content
                }
            })
        }


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

    fabricCanvas.value.on(
        'mouse:dblclick',
        handleTextDoubleClick
    )

    fabricCanvas.value.on(
        'path:created',
        event => {
            if (!isAnswerMode.value) return

            event.path.set(
                'cnRole',
                'student'
            )
        }
    )
}


/*
 * 現在のページをCanvasに表示
 */
const loadCurrentPage = async () => {

    const fc = fabricCanvas.value

    if (!fc) return

    const page = pages.value[currentPage.value]

    if (!page) return

    console.log(
        '表示するページ:',
        page
    )

    // Canvasを空にする
    fc.clear()

    let displayData

    /*
     * 回答モード・返却後の修正モード
     *
     * 先生の教材
     * ＋
     * 生徒の回答
     */
    if (
        isAnswerMode.value ||
        submissionStatus.value === 'returned'
    ) {

        const teacherObjects =
            page.canvasData?.objects ?? []

        const studentObjects =
            page.answerData?.objects ?? []

        displayData = {
            version: '7.4.0',
            objects: [
                ...teacherObjects,
                ...studentObjects
            ]
        }

        console.log(
            '先生オブジェクト数:',
            teacherObjects.length
        )

        console.log(
            '生徒オブジェクト数:',
            studentObjects.length
        )

    } else {

        /*
         * 閲覧モード
         *
         * 先生の教材だけ
         */
        displayData = page.canvasData
    }

    if (displayData) {

        console.log(
            '表示するCanvasデータ:',
            displayData
        )

        await fc.loadFromJSON(displayData)

        /*
         * 回答モードでは、
         *
         * 前半 = 先生
         * 後半 = 生徒
         *
         * として扱う
         */
        if (
            isAnswerMode.value ||
            submissionStatus.value === 'returned'
        ) {

            const teacherCount =
                page.canvasData?.objects?.length ?? 0

            fc.getObjects().forEach(
                (object, index) => {

                    if (index < teacherCount) {

                        object.set(
                            'cnRole',
                            'teacher'
                        )

                    } else {

                        object.set(
                            'cnRole',
                            'student'
                        )

                    }

                }
            )

        } else {

            /*
             * 閲覧モード
             */
            fc.getObjects().forEach(
                object => {

                    object.set(
                        'cnRole',
                        'teacher'
                    )

                }
            )

        }
    }

    /*
     * 閲覧モード
     *
     * 全オブジェクト編集禁止
     */
    if (!isAnswerMode.value) {

        fc.getObjects().forEach(
            object => {

                object.set({
                    selectable: false,
                    evented: false
                })

            }
        )

    } else {

        /*
         * 回答モード
         *
         * 先生 → 編集禁止
         * 生徒 → 編集可能
         */
        fc.getObjects().forEach(
            object => {

                if (
                    object.cnRole === 'teacher'
                ) {

                    object.set({
                        selectable: false,
                        evented: false
                    })

                } else if (
                    object.cnRole === 'student'
                ) {

                    object.set({
                        selectable: true,
                        evented: true
                    })

                }

            }
        )
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

    console.table(
        fc.getObjects().map(
            (object, index) => ({
                index,
                type: object.type,
                cnRole: object.cnRole,
                selectable: object.selectable,
                evented: object.evented
            })
        )
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

    // 生徒が作ったオブジェクトだけ保存
    const studentObjects = fc
        .getObjects()
        .filter(
            object =>
                object.cnRole === 'student'
        )

    page.answerData = {
        version: '7.4.0',
        objects: studentObjects.map(
            object => object.toObject()
        )
    }

    console.log(
        '生徒回答として保存:',
        page.answerData
    )
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
            content: page.answerData ?? {
                version: '7.4.0',
                objects: []
            }
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

        submissionStatus.value = 'draft'

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

        submissionStatus.value = 'submitted'

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

const withdrawSubmission = async () => {

    const confirmed = confirm(
        '提出を取り消して、回答を修正しますか？'
    )

    if (!confirmed) {
        return
    }

    try {

        const response = await fetch(
            `/student-lessons-json/${props.lesson.id}/submission/withdraw`,
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

        const data = await response.json()

        if (!response.ok) {
            throw new Error(
                data.message || '提出の取り消しに失敗しました。'
            )
        }

        console.log(
            '提出取り消し成功:',
            data
        )

        submissionStatus.value = 'draft'

        alert('提出を取り消しました。')

        // 回答モードに入る
        await startAnswer()

    } catch (err) {

        console.error(
            '提出取り消しエラー:',
            err
        )

        alert(err.message)
    }
}

/*
 * 生徒回答
 */
const startAnswer = async () => {

    isAnswerMode.value = true
    answerTool.value = 'select'

    if (fabricCanvas.value) {

        fabricCanvas.value.isDrawingMode = false

        // 保存済み回答を読み込む
        await loadCurrentPage()

        fabricCanvas.value.requestRenderAll()
    }
}

//再提出
const startReturnedAnswer = async () => {
    isAnswerMode.value = true
    answerTool.value = 'select'

    if (fabricCanvas.value) {
        fabricCanvas.value.isDrawingMode = false

        await loadCurrentPage()

        fabricCanvas.value.requestRenderAll()
    }
}

const cancelAnswer = async () => {
    isAnswerMode.value = false

    if (fabricCanvas.value) {
        fabricCanvas.value.isDrawingMode = false

        fabricCanvas.value.discardActiveObject()

        // 保存していない変更を破棄して、
        // 保存済みの答案を再読み込み
        await loadCurrentPage()

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

const handleTextDoubleClick = (event) => {
    if (!isAnswerMode.value) return

    const target = event.target

    if (
        target &&
        target.cnRole === 'student' &&
        target.type === 'i-text'
    ) {
        const fc = fabricCanvas.value

        if (!fc) return

        fc.setActiveObject(target)

        target.enterEditing()
        target.selectAll()

        fc.requestRenderAll()
    }
}

const handleCanvasClick = (event) => {
    if (!isAnswerMode.value) return

    const fc = fabricCanvas.value

    if (!fc) return

    const pointer = fc.getScenePoint(event.e)

    // 消しゴム
    if (answerTool.value === 'eraser') {
        const target = event.target

        console.log('消しゴム対象:', target)
        console.log('cnRole:', target?.cnRole)

        if (
            target &&
            target.cnRole === 'student'
        ) {
            fc.remove(target)
            fc.discardActiveObject()
            fc.requestRenderAll()

            console.log('生徒のオブジェクトを削除しました')
        }

        return
    }

    // 四角
    if (answerTool.value === 'rectangle') {
        const rect = new Rect({
            left: pointer.x,
            top: pointer.y,
            width: 120,
            height: 120,
            fill: '#dbeafe',
            stroke: '#2563eb',
            strokeWidth: 2,
            cnRole: 'student'
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
            fill: '#000000',
            cnRole: 'student'
        })

        fc.add(text)

        fc.setActiveObject(text)

        text.enterEditing()
        text.selectAll()

        fc.requestRenderAll()

        answerTool.value = 'select'
    }
}
const handleKeyDown = event => {
    if (!isAnswerMode.value) return

    const fc = fabricCanvas.value

    if (!fc) return

    const activeObject = fc.getActiveObject()

    // テキスト編集中なら、
    // Backspace / Delete は文字入力として使う
    if (activeObject?.isEditing) {
        return
    }

    const activeObjects = fc.getActiveObjects()

    if (
        (event.key === 'Delete' ||
            event.key === 'Backspace') &&
        activeObjects.length > 0
    ) {
        activeObjects.forEach(object => {
            if (object.cnRole === 'student') {
                fc.remove(object)
            }
        })

        fc.discardActiveObject()

        fc.requestRenderAll()
    }
}




/*
 * 終了時
 */
onMounted(async () => {

    window.addEventListener(
        'keydown',
        handleKeyDown
    )
    await loadLesson()
    await nextTick()
    initCanvas()
    await loadCurrentPage()
})

onUnmounted(() => {

    window.removeEventListener(
        'keydown',
        handleKeyDown
    )

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

.withdraw-button {
    padding: 10px 16px;
    border: 1px solid #f59e0b;
    border-radius: 10px;
    background: #fffbeb;
    color: #b45309;
    font-size: 14px;
    cursor: pointer;
}

.withdraw-button:hover {
    background: #fef3c7;
}

.grading-result {
    margin-top: 20px;
    padding: 24px;
    background: white;
    border-radius: 16px;
    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.05);
}

.grading-result h2 {
    margin-top: 0;
    color: #1f2937;
}

.score {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 15px;
    padding: 15px;
    background: #f3f4f6;
    border-radius: 10px;
}

.score span {
    color: #6b7280;
}

.score strong {
    font-size: 24px;
    color: #2563eb;
}

.comment {
    margin-top: 20px;
}

.comment h3 {
    margin-bottom: 10px;
}

.comment p {
    margin: 0;
    padding: 15px;
    background: #f9fafb;
    border-radius: 10px;
    white-space: pre-wrap;
    line-height: 1.7;
}

.returned-label {
    padding: 10px 16px;
    border-radius: 10px;
    background: #f5f3ff;
    color: #7c3aed;
    font-size: 14px;
    font-weight: 600;
}

.returned-area {
    display: flex;
    align-items: center;
    gap: 10px;
}

.edit-returned-button {
    padding: 10px 16px;
    border: 1px solid #8b5cf6;
    border-radius: 10px;
    background: #f5f3ff;
    color: #7c3aed;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.edit-returned-button:hover {
    background: #ede9fe;
}
</style>
