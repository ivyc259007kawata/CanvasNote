<template>

    <!-- クイズ回答画面 -->
    <div v-if="selectedQuiz && !quizResult" class="quiz-overlay">
        <div class="quiz-popup quiz-answer-popup">

            <h2>📝 {{ selectedQuiz.title }}</h2>

            <div v-for="(question, index) in selectedQuiz.questions" :key="question.id" class="quiz-question">
                <h3>
                    問題 {{ index + 1 }}
                </h3>

                <p class="question-text">
                    {{ question.question }}
                </p>

                <div class="quiz-options">

                    <label>
                        <input type="radio" :name="`question-${question.id}`" value="A"
                            v-model="quizAnswers[question.id]">
                        A. {{ question.option_a }}
                    </label>

                    <label>
                        <input type="radio" :name="`question-${question.id}`" value="B"
                            v-model="quizAnswers[question.id]">
                        B. {{ question.option_b }}
                    </label>

                    <label>
                        <input type="radio" :name="`question-${question.id}`" value="C"
                            v-model="quizAnswers[question.id]">
                        C. {{ question.option_c }}
                    </label>

                    <label>
                        <input type="radio" :name="`question-${question.id}`" value="D"
                            v-model="quizAnswers[question.id]">
                        D. {{ question.option_d }}
                    </label>

                </div>
            </div>

            <button class="quiz-submit-button" @click="submitQuiz">
                回答する
            </button>

        </div>
    </div>
    <div v-if="selectedQuiz && quizResult" class="quiz-overlay">
        <div class="quiz-popup quiz-result-popup">

            <h2>🎉 クイズ結果</h2>

            <p class="quiz-result-score">
                {{ quizResult.correct_count }} / {{ quizResult.total_count }} 問正解
            </p>

            <p class="quiz-result-rate">
                正答率：{{ quizResult.score }}%
            </p>

            <button class="quiz-close-button" @click="closeQuiz">
                閉じる
            </button>

        </div>
    </div>


    <!-- クイズ出題ポップアップ -->
    <div v-if="activeQuizzes.length > 0 && !selectedQuiz" class="quiz-overlay">
        <div class="quiz-popup">

            <h2>📢 クイズが出題されました！</h2>

            <div v-for="quiz in activeQuizzes" :key="quiz.id" class="quiz-popup-item">
                <h3>
                    {{ quiz.title }}
                </h3>

                <p>
                    {{ quiz.questions?.length ?? 0 }}問のクイズです。
                </p>

                <button class="quiz-answer-button" @click="startQuiz(quiz)">
                    回答する
                </button>
            </div>

        </div>
    </div>


    <!-- 教材閲覧画面 -->
    <StudentLessonViewer v-if="selectedLesson" :lesson="selectedLesson" @back="selectedLesson = null" />


    <!-- 生徒Dashboard -->
    <div v-else class="student-dashboard">

        <header class="dashboard-header">

            <div>

                <h1>📚 生徒ホーム</h1>

                <p>
                    ようこそ、{{ userName }} さん
                </p>

            </div>

        </header>


        <main class="dashboard-content">

            <h2>
                📖 くばられたもの一覧
            </h2>


            <p v-if="loading" class="message">
                教材を読み込んでいます...
            </p>


            <p v-else-if="lessons.length === 0" class="message">
                現在、配布されている教材はありません。
            </p>


            <div v-else class="lesson-list">

                <div v-for="lesson in lessons" :key="lesson.id" class="lesson-card">

                    <div class="lesson-info">

                        <h3>
                            {{ lesson.title }}
                        </h3>


                        <p v-if="lesson.description" class="description">
                            {{ lesson.description }}
                        </p>


                        <p class="teacher">
                            作成者：
                            {{ lesson.teacher?.name ?? '先生' }}
                        </p>


                        <!-- 提出状況 -->
                        <p class="submission-status">

                            状態：

                            <span v-if="!lesson.submission_status">
                                🟡 未提出
                            </span>

                            <span v-else-if="lesson.submission_status === 'draft'">
                                📝 下書き
                            </span>

                            <span v-else-if="lesson.submission_status === 'submitted'">
                                🟢 提出完了
                            </span>

                            <span v-else-if="lesson.submission_status === 'returned'">
                                🟣 採点しました！
                            </span>

                        </p>


                        <!-- 採点結果 -->
                        <p v-if="
                            lesson.submission_status === 'returned'
                            && lesson.submission_score !== null
                        " class="submission-score">
                            点数：
                            {{ lesson.submission_score }}点
                        </p>


                        <p v-if="
                            lesson.submission_status === 'returned'
                        " class="submission-comment-status">
                            💬
                            {{
                                lesson.submission_comment
                                    ? 'コメントあり'
                                    : 'コメントなし'
                            }}
                        </p>

                    </div>


                    <button class="open-button" @click="openLesson(lesson)">
                        📖 教材を見る
                    </button>

                </div>

            </div>

        </main>

    </div>

</template>



<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import StudentLessonViewer
    from './StudentLessonViewer.vue'

const userName = ref(
    window.Laravel?.user?.name ?? '生徒'
)

const lessons = ref([])
const selectedLesson = ref(null)
const loading = ref(true)
// 出題中のクイズ
const activeQuizzes = ref([])
let quizPollingTimer = null

const loadActiveQuizzes = async () => {
    try {
        const response = await fetch(
            '/student-active-quizzes-json'
        )

        if (!response.ok) {
            throw new Error(
                '出題中クイズの取得に失敗しました'
            )
        }

        activeQuizzes.value = await response.json()

    } catch (error) {
        console.error(
            '出題中クイズ取得エラー:',
            error
        )
    }
}
const selectedQuiz = ref(null)

// 生徒が選んだ回答
const quizAnswers = ref({})

// クイズの採点結果
const quizResult = ref(null)

const startQuiz = (quiz) => {
    selectedQuiz.value = quiz
    quizAnswers.value = {}
    quizResult.value = null
}

const submitQuiz = async () => {
    if (!selectedQuiz.value) {
        return
    }

    try {
        const response = await fetch(
            `/student-quizzes-json/${selectedQuiz.value.id}/submit`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content'),
                },
                body: JSON.stringify({
                    answers: quizAnswers.value,
                }),
            }
        )

        const data = await response.json()

        if (!response.ok) {
            throw new Error(
                data.message ??
                'クイズの送信に失敗しました'
            )
        }

        console.log('クイズ結果:', data)
        quizResult.value = data.result

        activeQuizzes.value = activeQuizzes.value.filter(
            quiz => quiz.id !== selectedQuiz.value.id
        )

    } catch (error) {
        console.error(
            'クイズ回答エラー:',
            error
        )
    }
}

const closeQuiz = () => {
    selectedQuiz.value = null
    quizResult.value = null
    quizAnswers.value = {}
}

const loadLessons = async () => {
    try {
        const response =
            await fetch('/student-lessons-json')

        if (!response.ok) {
            throw new Error(
                '公開教材の取得に失敗しました'
            )
        }

        lessons.value =
            await response.json()

    } catch (error) {
        console.error(
            '公開教材取得エラー:',
            error
        )
    } finally {
        loading.value = false
    }
}

const openLesson = (lesson) => {
    selectedLesson.value = lesson
}

onMounted(() => {
    loadLessons()
    loadActiveQuizzes()

    // 5秒ごとに公開中クイズを確認
    quizPollingTimer = setInterval(() => {
        // クイズ回答中は確認しなくてもよい
        if (!selectedQuiz.value) {
            loadActiveQuizzes()
        }
    }, 5000)
})
onUnmounted(() => {
    if (quizPollingTimer) {
        clearInterval(quizPollingTimer)
        quizPollingTimer = null
    }
})
</script>

<style scoped>
.student-dashboard {
    min-height: 100vh;
    background: #f5f7fb;
}

.dashboard-header {
    padding: 30px 40px;
    background: white;
    border-bottom: 1px solid #ddd;
}

.dashboard-header h1 {
    margin: 0;
    font-size: 28px;
}

.dashboard-header p {
    margin-top: 8px;
    color: #666;
}

.dashboard-content {
    padding: 40px;
}

.dashboard-content h2 {
    margin-bottom: 20px;
}

.message {
    padding: 30px;
    background: white;
    border-radius: 12px;
    color: #666;
}

.lesson-list {
    display: grid;
    grid-template-columns:
        repeat(auto-fill, minmax(280px, 1fr));

    gap: 20px;
}

.lesson-card {
    padding: 24px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.06);
}

.lesson-card h3 {
    margin: 0 0 12px;
    font-size: 20px;
}

.description {
    color: #555;
}

.teacher {
    margin-top: 16px;
    color: #777;
    font-size: 14px;
}

.open-button {
    margin-top: 20px;
    padding: 9px 16px;

    border: none;
    border-radius: 8px;

    background: #2563eb;
    color: white;

    cursor: pointer;
}

.open-button:hover {
    opacity: 0.9;
}

/* 提出状況 */
.submission-status {
    margin-top: 12px;
    font-size: 14px;
    color: #555;
}

/* 採点結果 */
.submission-score {
    margin-top: 8px;
    font-weight: bold;
    color: #2563eb;
}

/* 先生からのコメント */
.submission-comment-status {
    margin-top: 6px;
    font-size: 14px;
    color: #666;
}

/* クイズ出題ポップアップ */

.quiz-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.45);
}

.quiz-popup {
    width: min(500px, 90%);
    padding: 30px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.quiz-popup h2 {
    margin-top: 0;
    margin-bottom: 24px;
}

.quiz-popup-item {
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: #f8fafc;
}

.quiz-popup-item h3 {
    margin: 0 0 10px;
}

.quiz-popup-item p {
    margin-bottom: 16px;
    color: #666;
}

.quiz-answer-button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    cursor: pointer;
}

.quiz-answer-button:hover {
    opacity: 0.9;
}

/* クイズ回答画面 */

.quiz-answer-popup {
    max-height: 85vh;
    overflow-y: auto;
}

.quiz-question {
    margin-bottom: 24px;
    padding: 20px;

    border: 1px solid #ddd;
    border-radius: 12px;

    background: #f8fafc;
}

.quiz-question h3 {
    margin: 0 0 12px;
}

.question-text {
    margin-bottom: 16px;
    font-weight: bold;
}

.quiz-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quiz-options label {
    display: block;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: white;
    cursor: pointer;
}

.quiz-options label:hover {
    background: #f1f5f9;
}

.quiz-options input {
    margin-right: 8px;
}

.quiz-submit-button {
    width: 100%;
    margin-top: 10px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

.quiz-submit-button:hover {
    opacity: 0.9;
}

/* クイズ結果 */

.quiz-result-popup {
    text-align: center;
}

.quiz-result-popup h2 {
    margin-bottom: 24px;
}

.quiz-result-score {
    margin: 20px 0 10px;
    font-size: 28px;
    font-weight: bold;
}

.quiz-result-rate {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
    color: #2563eb;
}

.quiz-close-button {
    width: 100%;
    margin-top: 24px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    background: #666;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

.quiz-close-button:hover {
    opacity: 0.9;
}
</style>
