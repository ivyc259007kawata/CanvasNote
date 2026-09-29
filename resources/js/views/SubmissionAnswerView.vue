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

        <!-- 採点 -->
        <div class="grading-area">
            <h3>📝 採点</h3>

            <div class="score-input">
                <label>
                    点数：
                    <input v-model.number="score" type="number" min="0" max="100" />
                    / 100
                </label>
            </div>

            <div class="comment-input">
                <label>
                    💬 コメント
                </label>

                <textarea v-model="comment" placeholder="生徒へのコメントを入力してください"></textarea>
            </div>

            <button class="grade-save-button" @click="saveGrade">
                💾 採点を保存
            </button>

            <button class="return-button" @click="returnSubmission">
                📩 生徒に返却する
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

// 採点
const score = ref(null)
const comment = ref('')

// 全ページ数
const totalPages = computed(() => {
    return props.submission?.elements?.length ?? 0
})

const loadPage = async (pageNumber) => {

    // =========================
    // 生徒の提出データ
    // =========================

    const submissionPage =
        props.submission.elements?.find(
            element =>
                element.page_number === pageNumber
        )

    if (!submissionPage) {
        console.log(
            `${pageNumber}ページ目の回答がありません`
        )
        return
    }


    // =========================
    // 先生の教材データを取得
    // =========================

    let teacherPage = null

    try {

        const response = await fetch(
            `/lessons-json/${props.submission.lesson_id}/canvas`,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {
            throw new Error(
                '先生の教材データの取得に失敗しました'
            )
        }

        const data = await response.json()

        teacherPage =
            data.pages?.find(
                page =>
                    page.page_number === pageNumber
            )

    } catch (error) {

        console.error(
            '先生の教材取得エラー:',
            error
        )

        alert(
            '先生の教材データを取得できませんでした'
        )

        return
    }


    // =========================
    // Canvasを空にする
    // =========================

    canvas.clear()


    // =========================
    // 先生の教材オブジェクト
    // =========================

    const teacherObjects =
        teacherPage?.content?.objects?.map(
            object => ({
                ...object,
                cnRole: 'teacher'
            })
        ) ?? []


    // =========================
    // 生徒の回答オブジェクト
    // =========================

    const studentObjects =
        submissionPage.content?.objects?.map(
            object => ({
                ...object,
                cnRole: 'student'
            })
        ) ?? []


    // =========================
    // 先生＋生徒を合成
    // =========================

    const combinedData = {
        version: '7.4.0',
        objects: [
            ...teacherObjects,
            ...studentObjects
        ]
    }


    // =========================
    // Canvasへ読み込む
    // =========================

    await canvas.loadFromJSON(
        combinedData
    )


    // =========================
    // 全オブジェクトを編集不可
    // =========================

    canvas.selection = false

    canvas.forEachObject(object => {

        object.selectable = false
        object.evented = false

    })


    // =========================
    // 表示更新
    // =========================

    canvas.renderAll()


    // =========================
    // 現在ページを更新
    // =========================

    currentPage.value = pageNumber


    // =========================
    // 確認用ログ
    // =========================

    console.log(
        `${pageNumber}ページ目を表示しました`
    )

    console.table(
        canvas.getObjects().map(
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

    // 保存済みの採点結果を読み込む
    score.value = props.submission.score ?? null
    comment.value = props.submission.comment ?? ''

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

const saveGrade = async () => {
    try {
        const response = await fetch(
            `/submissions/${props.submission.id}/grade`,
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
                    score: score.value,
                    comment: comment.value
                })
            }
        )

        const data = await response.json()

        if (!response.ok) {
            throw new Error(
                data.message || '採点の保存に失敗しました。'
            )
        }

        console.log('採点保存成功:', data)

        alert('採点を保存しました。')

    } catch (err) {
        console.error('採点保存エラー:', err)
        alert(err.message)
    }
}

const returnSubmission = async () => {
    const confirmed = confirm(
        '採点結果を生徒に返却しますか？'
    )

    if (!confirmed) {
        return
    }

    try {
        const response = await fetch(
            `/submissions/${props.submission.id}/return`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
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
                data.message || '返却に失敗しました。'
            )
        }

        console.log(
            '返却成功:',
            data
        )

        alert('採点結果を返却しました。')
    } catch (err) {
        console.error(
            '返却エラー:',
            err
        )

        alert(err.message)
    }
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

.grading-area {
    margin-top: 30px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
}

.grading-area h3 {
    margin-top: 0;
}

.score-input {
    margin-bottom: 20px;
}

.score-input input {
    width: 80px;
    margin: 0 8px;
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 16px;
}

.comment-input {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.comment-input textarea {
    width: 100%;
    min-height: 100px;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    resize: vertical;
    box-sizing: border-box;
    font-size: 14px;
}

.grade-save-button {
    margin-top: 15px;
}

.return-button {
    margin-top: 10px;
    background: #8b5cf6;
}

.return-button:hover {
    opacity: 0.85;
}
</style>