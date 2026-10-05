<template>

    <div class="quiz-page">
        <!-- =========================
            ヘッダー
        ========================= -->
        <div class="page-header">
            <div>
                <h1>❓ クイズ</h1>
                <p>教材に紐づけてクイズを作成・管理できます。</p>
            </div>

            <button class="create-button"
                @click="isEditMode = false; editingQuizId = null; resetForm(); showCreateForm = true">
                ＋ クイズを作成
            </button>
        </div>

        <!-- =========================
            クイズ一覧
        ========================== -->

        <div class="quiz-list">

            <div v-if="quizzes.length === 0" class="empty-state">
                <div class="empty-icon">❓</div>

                <h2>クイズはまだありません</h2>

                <p>
                    「＋ クイズを作成」からクイズを作成できます。
                </p>
            </div>

            <div v-for="quiz in quizzes" :key="quiz.id" class="quiz-card">

                <div class="quiz-info">

                    <h2>{{ quiz.title }}</h2>

                    <p>
                        教材：
                        {{ quiz.lesson?.title ?? '教材なし' }}
                    </p>

                    <span>
                        問題数：{{ quiz.questions_count }}問
                    </span>

                    <p>
                        対象クラス：

                        <span v-if="
                            quiz.classes &&
                            quiz.classes.length
                        ">
                            {{
                                quiz.classes
                                    .map(
                                        classItem =>
                                            classItem.name
                                    )
                                    .join('、')
                            }}
                        </span>

                        <span v-else>
                            未設定
                        </span>
                    </p>

                </div>


                <div class="quiz-status">

                    <!-- 編集 -->
                    <button v-if="quiz.status === 'draft'" class="edit-button" @click="openEditForm(quiz)">
                        編集
                    </button>


                    <!-- 下書き -->
                    <template v-if="quiz.status === 'draft'">

                        <button class="publish-button" @click="activateQuiz(quiz)">
                            出題する
                        </button>

                    </template>


                    <!-- 出題中 -->
                    <template v-else-if="quiz.status === 'active'">

                        <div class="active-actions">

                            <span class="status-active">
                                出題中
                            </span>

                            <button class="unpublish-button" @click="deactivateQuiz(quiz)">
                                非公開にする
                            </button>

                        </div>

                    </template>


                    <!-- 終了 -->
                    <span v-else-if="quiz.status === 'ended'" class="status-ended">
                        終了
                    </span>


                    <!-- 結果 -->
                    <button class="result-button" @click="showQuizResults(quiz)">
                        結果を見る
                    </button>

                </div>

            </div>

        </div>




        <!-- =========================
             クイズ作成
        ========================== -->

        <div v-if="showCreateForm" class="modal-overlay" @click.self="closeCreateForm">

            <div class="modal">

                <div class="modal-header">

                    <h2>
                        {{ isEditMode ? 'クイズを編集' : 'クイズを作成' }}
                    </h2>

                    <button class="close-button" @click="closeCreateForm">
                        ×
                    </button>

                </div>


                <!-- 教材 -->

                <div class="form-group">

                    <label>
                        教材
                    </label>

                    <select v-model="form.lesson_id">

                        <option value="">
                            教材を選択してください
                        </option>

                        <option v-for="lesson in lessons.filter(lesson => lesson.can_edit)" :key="lesson.id"
                            :value="lesson.id">
                            {{ lesson.title }}
                        </option>

                    </select>

                </div>
                <!-- 出題先クラス -->
                <div class="form-group">
                    <label>
                        出題先クラス
                    </label>
                    <div class="class-checkboxes">
                        <label v-for="schoolClass in classes" :key="schoolClass.id" class="class-checkbox">
                            <input type="checkbox" :value="schoolClass.id" v-model="form.class_ids">
                            {{ schoolClass.name }}
                        </label>
                    </div>
                </div>

                <!-- 出題形式 -->
                <div class="form-group">
                    <label>
                        出題形式
                    </label>

                    <select v-model="form.mode">
                        <option value="single">
                            1問リアルタイムクイズ
                        </option>

                        <option value="test">
                            複数問の小テスト
                        </option>
                    </select>
                </div>

                <!-- クイズタイトル -->

                <div class="form-group">
                    <label>
                        クイズタイトル
                    </label>
                    <input v-model="form.title" type="text" placeholder="例：一次方程式の確認クイズ">
                </div>

                <!-- 問題 -->
                <div v-for="(question, index) in form.questions" :key="index" class="question-box">
                    <div class="question-header">

                        <h3>
                            問題{{ index + 1 }}
                        </h3>

                        <button v-if="form.questions.length > 1" type="button" class="delete-question-button"
                            @click="removeQuestion(index)">
                            削除
                        </button>

                    </div>

                    <div class="form-group">
                        <label>
                            問題文
                        </label>

                        <textarea v-model="question.question" rows="3" placeholder="問題文を入力してください"></textarea>
                    </div>

                    <div class="options">

                        <div class="form-group">
                            <label>A</label>

                            <input v-model="question.option_a" type="text">
                        </div>

                        <div class="form-group">
                            <label>B</label>

                            <input v-model="question.option_b" type="text">
                        </div>

                        <div class="form-group">
                            <label>C</label>

                            <input v-model="question.option_c" type="text">
                        </div>

                        <div class="form-group">
                            <label>D</label>

                            <input v-model="question.option_d" type="text">
                        </div>

                    </div>

                    <!-- 正解 -->
                    <div class="form-group">

                        <label>
                            正解
                        </label>

                        <select v-model="question.correct_answer">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>

                    </div>
                </div>

                <!-- 問題追加 -->
                <button type="button" class="add-question-button" @click="addQuestion">
                    ＋ 問題を追加
                </button>


                <!-- ボタン -->

                <div class="modal-actions">

                    <!-- 編集中だけ削除ボタンを表示 -->
                    <button v-if="isEditMode" class="delete-quiz-button" @click="deleteQuiz">
                        クイズを削除
                    </button>

                    <button class="cancel-button" @click="closeCreateForm">
                        キャンセル
                    </button>

                    <button class="save-button" @click="isEditMode ? updateQuiz() : createQuiz()">
                        {{ isEditMode ? '変更を保存' : 'クイズを保存' }}
                    </button>

                </div>

            </div>

        </div>
        <!-- =========================
     クイズ結果
========================== -->
        <div v-if="showResults" class="modal-overlay" @click.self="closeResults">
            <div class="modal">

                <div class="modal-header">
                    <h2>
                        クイズ結果
                    </h2>

                    <button class="close-button" @click="closeResults">
                        ×
                    </button>
                </div>

                <div v-if="loadingResults">
                    結果を読み込んでいます...
                </div>

                <div v-else-if="selectedQuizResult" class="result-content">

                    <h3>
                        {{ selectedQuizResult.quiz.title }}
                    </h3>

                    <p>
                        対象クラス：
                        {{
                            selectedQuizResult.classes
                                .map(classItem => classItem.name)
                                .join('、')
                        }}
                    </p>

                    <div class="answer-summary">

                        <div class="summary-label">
                            回答状況
                        </div>

                        <div class="summary-count">
                            {{ selectedQuizResult.answered_students }}
                            /
                            {{ selectedQuizResult.total_students }}
                            人回答済み
                        </div>

                    </div>

                    <div class="result-list">

                        <div v-for="student in selectedQuizResult.students" :key="student.id" class="result-row">
                            <div class="student-info">

                                <div class="student-name">
                                    {{ student.name }}
                                </div>

                                <span v-if="student.answered" class="answer-status answered">
                                    回答済み
                                </span>

                                <span v-else class="answer-status unanswered">
                                    未回答
                                </span>

                            </div>

                            <div v-if="student.answered" class="student-score">
                                <span>
                                    {{ student.correct_count }}
                                    /
                                    {{ student.total_count }}問正解
                                </span>

                                <strong>
                                    {{ student.score }}%
                                </strong>
                            </div>

                            <div v-else class="student-score unanswered-score">
                                —
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-actions">
                    <button class="cancel-button" @click="closeResults">
                        閉じる
                    </button>
                </div>

            </div>
        </div>
    </div>

</template>


<script setup>

import { ref, onMounted } from 'vue'


// =========================
// データ
// =========================

const quizzes = ref([])
const lessons = ref([])
const classes = ref([])

// =========================
// クイズ結果
// =========================
const showResults = ref(false)
const selectedQuizResult = ref(null)
const loadingResults = ref(false)

// =========================
// 作成画面
// =========================

const showCreateForm = ref(false)

// =========================
// クイズ編集
// =========================

const isEditMode = ref(false)
const editingQuizId = ref(null)


// =========================
// クイズ入力
// =========================

const form = ref({

    lesson_id: '',
    title: '',
    mode: 'single',
    class_ids: [],
    questions: [

        {
            question: '',
            option_a: '',
            option_b: '',
            option_c: '',
            option_d: '',
            correct_answer: 'A'
        }

    ]

})


// =========================
// クイズ一覧取得
// =========================

async function loadQuizzes() {

    try {

        const response =
            await fetch('/quizzes-json')

        if (!response.ok) {
            throw new Error('クイズ一覧の取得に失敗しました')
        }

        quizzes.value =
            await response.json()

    } catch (error) {

        console.error(error)

    }

}


// =========================
// 教材一覧取得
// =========================

async function loadLessons() {

    try {

        const response =
            await fetch('/lessons-json')

        if (!response.ok) {
            throw new Error('教材一覧の取得に失敗しました')
        }

        lessons.value =
            await response.json()

        console.log(
            '取得した教材一覧:',
            lessons.value
        )

    } catch (error) {

        console.error(error)

    }

}
// =========================
// クラス一覧取得
// =========================
async function loadClasses() {
    try {
        const response = await fetch('/classes-json')

        if (!response.ok) {
            throw new Error('クラス一覧の取得に失敗しました')
        }

        classes.value = await response.json()

        console.log(
            '取得したクラス一覧:',
            classes.value
        )
    } catch (error) {
        console.error(error)
    }
}


// =========================
// 作成画面を閉じる
// =========================

function closeCreateForm() {
    showCreateForm.value = false
    isEditMode.value = false
    editingQuizId.value = null
    resetForm()
}

function resetForm() {
    form.value = {
        lesson_id: '',
        title: '',
        mode: 'single',
        class_ids: [],
        questions: [
            {
                question: '',
                option_a: '',
                option_b: '',
                option_c: '',
                option_d: '',
                correct_answer: 'A'
            }
        ]
    }
}

function addQuestion() {
    form.value.questions.push({
        question: '',
        option_a: '',
        option_b: '',
        option_c: '',
        option_d: '',
        correct_answer: 'A'
    })
}

function removeQuestion(index) {
    if (form.value.questions.length <= 1) {
        return
    }

    form.value.questions.splice(index, 1)
}

// =========================
// クイズ編集画面を開く
// =========================

async function openEditForm(quiz) {

    // 公開中なら編集不可
    if (quiz.status === 'active') {
        alert(
            '公開中のクイズは編集できません。\n\n先に「非公開にする」を押してください。'
        )
        return
    }

    try {

        const response = await fetch(
            `/quizzes-json/${quiz.id}`
        )

        const data = await response.json()

        if (!response.ok) {
            alert(
                data.message ??
                'クイズ情報の取得に失敗しました'
            )
            return
        }

        // 編集対象を保存
        editingQuizId.value = quiz.id
        isEditMode.value = true

        // 取得した内容をフォームへ入れる
        form.value = {
            lesson_id: data.lesson_id ?? '',
            title: data.title ?? '',
            mode: data.mode ?? 'single',

            class_ids: data.classes
                ? data.classes.map(
                    schoolClass => schoolClass.id
                )
                : [],

            questions: data.questions?.map(
                question => ({
                    question: question.question ?? '',
                    option_a: question.option_a ?? '',
                    option_b: question.option_b ?? '',
                    option_c: question.option_c ?? '',
                    option_d: question.option_d ?? '',
                    correct_answer:
                        question.correct_answer ?? 'A'
                })
            ) ?? []
        }

        showCreateForm.value = true

    } catch (error) {

        console.error(
            'クイズ編集データ取得エラー:',
            error
        )

        alert(
            'クイズ情報の取得に失敗しました'
        )
    }
}

// =========================
// クイズ作成
// =========================

async function createQuiz() {

    console.log('送信するフォーム:', form.value)

    try {

        const response =
            await fetch('/quizzes-json', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content')
                },

                body: JSON.stringify(form.value)

            })


        const text = await response.text()
        console.log('クイズ保存レスポンス:', response.status, text)
        let data
        try {
            data = JSON.parse(text)
        } catch (error) {
            console.error('JSONとして解析できませんでした:', text)
            alert('サーバーからJSONではないレスポンスが返ってきました')
            return
        }


        if (!response.ok) {
            console.error(data)
            alert(
                data.message ??
                'クイズの作成に失敗しました'
            )
            return
        }

        alert('クイズを作成しました')

        // 一覧を更新
        await loadQuizzes()

        // フォームを閉じる
        showCreateForm.value = false

        // 入力内容をリセット

        form.value = {
            lesson_id: '',
            title: '',
            mode: 'single',
            class_ids: [],
            questions: [
                {
                    question: '',
                    option_a: '',
                    option_b: '',
                    option_c: '',
                    option_d: '',
                    correct_answer: 'A'
                }
            ]
        }

    } catch (error) {
        console.error(error)
        alert('通信エラーが発生しました')
    }
}

// =========================
// クイズ更新
// =========================

async function updateQuiz() {

    if (!editingQuizId.value) {
        return
    }

    console.log(
        '更新するクイズ:',
        form.value
    )

    try {

        const response = await fetch(
            `/quizzes-json/${editingQuizId.value}`,
            {
                method: 'PUT',

                headers: {
                    'Content-Type': 'application/json',

                    'Accept': 'application/json',

                    'X-CSRF-TOKEN':
                        document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            ?.getAttribute(
                                'content'
                            )
                },

                body: JSON.stringify(
                    form.value
                )
            }
        )

        const text = await response.text()

        console.log(
            'クイズ更新レスポンス:',
            response.status,
            text
        )

        let data

        try {
            data = JSON.parse(text)
        } catch (error) {

            console.error(
                'JSONとして解析できませんでした:',
                text
            )

            alert(
                'サーバーからJSONではないレスポンスが返ってきました'
            )

            return
        }

        if (!response.ok) {

            console.error(data)

            alert(
                data.message ??
                'クイズの更新に失敗しました'
            )

            return
        }

        alert(
            'クイズを更新しました'
        )

        // 一覧を更新
        await loadQuizzes()

        // モーダルを閉じる
        closeCreateForm()

    } catch (error) {

        console.error(
            'クイズ更新エラー:',
            error
        )

        alert(
            '通信エラーが発生しました'
        )
    }
}

//クイズの削除
async function deleteQuiz() {
    if (!editingQuizId.value) {
        return
    }

    const confirmed = window.confirm(
        `「${form.value.title}」を削除しますか？\n\nこのクイズに生徒の回答がある場合、回答結果も一緒に削除されます。\n\nこの操作は元に戻せません。`
    )

    if (!confirmed) {
        return
    }

    try {
        const response = await fetch(
            `/quizzes-json/${editingQuizId.value}`,
            {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN':
                        document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            ?.getAttribute('content'),
                    'Accept': 'application/json'
                }
            }
        )

        const text = await response.text()

        console.log(
            'クイズ削除レスポンス:',
            response.status,
            text
        )

        let data

        try {
            data = JSON.parse(text)
        } catch (error) {
            console.error(
                'JSONとして解析できませんでした:',
                text
            )

            alert(
                'サーバーからJSONではないレスポンスが返ってきました'
            )

            return
        }

        if (!response.ok) {
            alert(
                data.message ??
                'クイズの削除に失敗しました'
            )

            return
        }

        alert('クイズを削除しました')

        await loadQuizzes()

        closeCreateForm()

    } catch (error) {

        console.error(
            'クイズ削除エラー:',
            error
        )

        alert(
            '通信エラーが発生しました'
        )
    }
}

const activateQuiz = async (quiz) => {
    const response = await fetch(
        `/quizzes-json/${quiz.id}/activate`,
        {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }
    )

    const data = await response.json()

    if (!response.ok) {
        alert(data.message ?? 'クイズの出題に失敗しました')
        return
    }

    alert('クイズを出題しました')

    await loadQuizzes()
}

const deactivateQuiz = async (quiz) => {
    const confirmed = window.confirm(
        `「${quiz.title}」を非公開にしますか？\n\n非公開にすると、生徒側には表示されなくなります。`
    )

    if (!confirmed) {
        return
    }

    try {
        const response = await fetch(
            `/quizzes-json/${quiz.id}/deactivate`,
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':
                        document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            ?.getAttribute('content'),

                    'Content-Type': 'application/json',

                    'Accept': 'application/json'
                }
            }
        )

        const data = await response.json()

        if (!response.ok) {
            alert(
                data.message ??
                'クイズの非公開に失敗しました'
            )
            return
        }

        alert('クイズを非公開にしました')

        await loadQuizzes()

    } catch (error) {
        console.error(
            'クイズ非公開エラー:',
            error
        )

        alert(
            '通信エラーが発生しました'
        )
    }
}

// =========================
// クイズ結果取得
// =========================
async function showQuizResults(quiz) {
    loadingResults.value = true

    try {
        const response = await fetch(
            `/quizzes-json/${quiz.id}/results`
        )

        if (!response.ok) {
            throw new Error('クイズ結果の取得に失敗しました')
        }

        selectedQuizResult.value = await response.json()

        showResults.value = true
    } catch (error) {
        console.error(error)
        alert('クイズ結果の取得に失敗しました')
    } finally {
        loadingResults.value = false
    }
}
function closeResults() {
    showResults.value = false
    selectedQuizResult.value = null
}

// =========================
// 初期処理
// =========================

onMounted(() => {
    loadQuizzes()
    loadLessons()
    loadClasses()
})

</script>


<style scoped>
.quiz-page {
    min-height: 100vh;
    padding: 32px;
    background: #f8fafc;
    box-sizing: border-box;
}


/* =========================
   ヘッダー
========================= */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}


.page-header h1 {
    margin: 0 0 8px;
    font-size: 28px;
}


.page-header p {
    margin: 0;
    color: #64748b;
}


/* =========================
   作成ボタン
========================= */

.create-button {
    padding: 12px 18px;
    border: none;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}


.create-button:hover {
    background: #1d4ed8;
}


/* =========================
   クイズ一覧
========================= */

.quiz-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}


.quiz-card {
    padding: 20px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}


.quiz-info h2 {
    margin: 0 0 8px;
    font-size: 18px;
}


.quiz-info p {
    margin: 0 0 8px;
    color: #64748b;
}


.quiz-info span {
    color: #475569;
    font-size: 14px;
}


/* =========================
   空状態
========================= */

.empty-state {
    padding: 70px 20px;
    text-align: center;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}


.empty-icon {
    margin-bottom: 15px;
    font-size: 50px;
}


.empty-state h2 {
    margin: 0 0 8px;
    font-size: 20px;
}


.empty-state p {
    margin: 0;
    color: #64748b;
}


/* =========================
   モーダル
========================= */

.modal-overlay {
    position: fixed;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1000;
}


.modal {
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 25px;
    background: white;
    border-radius: 12px;
    box-sizing: border-box;
}


.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}


.modal-header h2 {
    margin: 0;
}


.close-button {
    border: none;
    background: transparent;
    font-size: 28px;
    color: #64748b;
    cursor: pointer;
}


/* =========================
   フォーム
========================= */

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 18px;
}


.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
}


.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 14px;
    box-sizing: border-box;
}


.form-group textarea {

    resize: vertical;

}

.class-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #f8fafc;

}


.class-checkbox {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: normal;
    cursor: pointer;

}


.class-checkbox input {
    width: auto;
}


.question-box {
    margin-top: 25px;
    padding: 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

.question-header h3 {
    margin: 0;
    font-size: 17px;
}

.options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}


/* =========================
   ボタン
========================= */

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 25px;
}

.cancel-button,
.save-button {
    padding: 10px 18px;
    border-radius: 7px;
    font-size: 14px;
    cursor: pointer;
}

.cancel-button {
    border: 1px solid #cbd5e1;
    background: white;
    color: #475569;
}

.save-button {
    border: none;
    background: #2563eb;
    color: white;
    font-weight: 600;
}

.save-button:hover {
    background: #1d4ed8;
}

.quiz-status {
    margin-top: 15px;
}

.status-active {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    background: #dcfce7;
    color: #166534;
    font-size: 13px;
    font-weight: 600;
}

.status-ended {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    background: #e2e8f0;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
}

/* =========================
   クイズ結果
========================= */

.result-content {
    margin-top: 8px;
}

.result-content h3 {
    margin: 0 0 12px;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.result-content>p {
    margin: 0 0 20px;
    color: #6b7280;
    font-size: 14px;
}

.result-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 400px;
    overflow-y: auto;
}

.result-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 16px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
}

.student-name {
    font-weight: 600;
    color: #374151;
}

.result-row>div:last-child {
    color: #374151;
    font-size: 14px;
    font-weight: 500;
}

.unanswered {
    color: #9ca3af !important;
    font-weight: 500;
}

.result-button {
    padding: 8px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #ffffff;
    color: #374151;
    cursor: pointer;
    font-size: 14px;
}

.result-button:hover {
    background: #f3f4f6;
}

.answer-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 14px 16px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
}

.summary-label {
    color: #6b7280;
    font-size: 14px;
}

.summary-count {
    color: #374151;
    font-size: 16px;
    font-weight: 600;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.answer-status {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.answer-status.answered {
    background: #e8f5e9;
    color: #2e7d32;
}

.answer-status.unanswered {
    background: #f3f4f6;
    color: #9ca3af;
}

.student-score {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #374151;
    font-size: 14px;
}

.student-score strong {
    min-width: 48px;

    font-size: 16px;
    text-align: right;
}

.unanswered-score {
    color: #9ca3af;
}

.question-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.delete-question-button {
    padding: 5px 10px;
    border: 1px solid #fecaca;
    border-radius: 6px;
    background: #fff;
    color: #dc2626;
    font-size: 12px;
    cursor: pointer;
}

.delete-question-button:hover {
    background: #fef2f2;
}

.add-question-button {
    width: 100%;
    margin-top: 14px;
    padding: 11px;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.add-question-button:hover {
    background: #f1f5f9;
}

.publish-button {
    padding: 8px 14px;
    border: none;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.publish-button:hover {
    background: #1d4ed8;
}

.active-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.unpublish-button {
    padding: 8px 14px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: white;
    color: #dc2626;
    font-size: 13px;
    cursor: pointer;
}

.unpublish-button:hover {
    background: #fef2f2;
}

.edit-button {
    padding: 8px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.edit-button:hover {
    background: #f3f4f6;
}

.delete-quiz-button {
    margin-right: auto;
    padding: 9px 14px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: white;
    color: #dc2626;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.delete-quiz-button:hover {
    background: #fef2f2;
}
</style>
