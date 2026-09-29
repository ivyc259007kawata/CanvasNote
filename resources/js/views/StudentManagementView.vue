<template>
    <div class="student-management-page">

        <!-- =========================
             ヘッダー
        ========================= -->
        <header class="page-header">

            <div>
                <h1>
                    👥 生徒
                </h1>

                <p>
                    生徒の一覧を確認できます
                </p>
            </div>

            <button class="add-button" @click="openStudentModal">
                ＋ 生徒を追加
            </button>

        </header>


        <!-- =========================
             生徒一覧
        ========================= -->
        <!-- =========================
     生徒一覧・詳細
========================= -->
        <section class="student-section">

            <!-- =========================
         生徒詳細
    ========================= -->
            <div v-if="selectedStudent" class="student-detail">

                <!-- 戻る -->
                <button class="back-button" @click="closeStudentDetail">
                    ← 生徒一覧に戻る
                </button>

                <!-- 読み込み中 -->
                <div v-if="detailLoading" class="loading-message">
                    生徒情報を読み込んでいます...
                </div>

                <!-- 詳細表示 -->
                <div v-else-if="studentDetail" class="detail-content">

                    <!-- =========================
                 基本情報
            ========================= -->
                    <div class="detail-header">

                        <div class="student-icon">
                            👤
                        </div>

                        <div>
                            <h2>
                                {{ studentDetail.student.name }}
                            </h2>

                            <p>
                                {{ studentDetail.student.email }}
                            </p>
                        </div>

                        <button class="delete-student-button" @click="deleteStudent">
                            🗑 生徒アカウントを削除
                        </button>

                    </div>


                    <!-- =========================
                 所属クラス
            ========================= -->
                    <section class="detail-section">
                        <div class="section-header">
                            <h3>
                                🏫 所属クラス
                            </h3>

                            <button class="add-class-button" @click="openClassModal">
                                ＋ クラスを追加
                            </button>
                        </div>

                        <div v-if="studentDetail.classes.length > 0" class="class-list">
                            <div v-for="schoolClass in studentDetail.classes" :key="schoolClass.id" class="class-item">
                                <span>
                                    {{ schoolClass.grade }}年
                                    {{ schoolClass.name }}
                                </span>

                                <button class="remove-class-button" @click="removeStudentFromClass(schoolClass)">
                                    ×
                                </button>
                            </div>
                        </div>

                        <p v-else class="no-data">
                            所属しているクラスはありません。
                        </p>
                    </section>


                    <!-- =========================
                 提出状況
            ========================= -->
                    <section class="detail-section">

                        <h3>
                            📋 提出状況
                        </h3>

                        <div v-if="studentDetail.submissions.length > 0" class="submission-list">

                            <div v-for="submission in studentDetail.submissions" :key="submission.id"
                                class="submission-item">

                                <!-- 課題名 -->
                                <div class="submission-main">

                                    <strong class="submission-title">
                                        {{ submission.lesson_title }}
                                    </strong>

                                    <!-- 提出状態 -->
                                    <div class="submission-status">

                                        <span v-if="submission.status === 'submitted'" class="status submitted">
                                            🟢 提出済み
                                        </span>

                                        <span v-else-if="submission.status === 'returned'" class="status returned">
                                            🟣 返却済み
                                        </span>

                                        <span v-else class="status draft">
                                            📝 下書き
                                        </span>

                                    </div>

                                </div>


                                <!-- 提出情報 -->
                                <div class="submission-detail">

                                    <!-- 提出日時 -->
                                    <div v-if="submission.submitted_at" class="submission-date">
                                        提出日時：
                                        {{ formatDateTime(submission.submitted_at) }}
                                    </div>

                                    <!-- 点数 -->
                                    <div v-if="submission.score !== null" class="submission-score">
                                        {{ submission.score }} / 100
                                    </div>

                                    <div v-else class="submission-score no-score">
                                        未採点
                                    </div>

                                    <!-- コメント -->
                                    <div v-if="submission.comment" class="submission-comment">
                                        <span class="comment-label">
                                            コメント
                                        </span>

                                        <p>
                                            {{ submission.comment }}
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <p v-else class="no-data">
                            まだ提出履歴はありません。
                        </p>

                    </section>

                </div>

            </div>


            <!-- =========================
         生徒一覧
    ========================= -->
            <div v-else>

                <div v-if="loading" class="loading-message">
                    生徒情報を読み込んでいます...
                </div>

                <div v-else-if="students.length > 0" class="student-list">

                    <div v-for="student in students" :key="student.id" class="student-card"
                        @click="openStudent(student)">

                        <div class="student-icon">
                            👤
                        </div>

                        <div class="student-info">

                            <h2>
                                {{ student.name }}
                            </h2>

                            <p>
                                {{ student.email }}
                            </p>

                        </div>

                        <div class="student-arrow">
                            →
                        </div>

                    </div>

                </div>

                <div v-else class="empty-message">
                    生徒がまだ登録されていません。
                </div>

            </div>

        </section>


        <!-- =========================
            生徒追加モーダル
        ========================= -->
        <div v-if="showStudentModal" class="modal-overlay" @click.self="closeStudentModal">
            <div class="modal">

                <div class="modal-header">
                    <h2>
                        ＋ 生徒アカウントを作成
                    </h2>

                    <button class="close-button" @click="closeStudentModal">
                        ×
                    </button>
                </div>

                <p class="modal-description">
                    新しい生徒のアカウントを作成します。
                </p>

                <!-- 名前 -->
                <div class="form-group">
                    <label>
                        名前
                    </label>

                    <input v-model="studentForm.name" type="text" placeholder="例：山田太郎">
                </div>

                <!-- メールアドレス -->
                <div class="form-group">
                    <label>
                        メールアドレス
                    </label>

                    <input v-model="studentForm.email" type="email" placeholder="例：yamada@example.com">
                </div>

                <!-- パスワード -->
                <div class="form-group">
                    <label>
                        パスワード
                    </label>

                    <input v-model="studentForm.password" type="password" placeholder="8文字以上">
                </div>

                <!-- エラー -->
                <p v-if="formError" class="form-error">
                    {{ formError }}
                </p>

                <!-- 作成ボタン -->
                <button class="create-button" :disabled="creating" @click="createStudent">
                    {{ creating ? '作成中...' : '生徒アカウントを作成' }}
                </button>

            </div>
        </div>


    </div>
</template>


<script setup>
import {
    ref,
    onMounted
} from 'vue'


// =========================
// 生徒一覧
// =========================

const students = ref([])

const loading = ref(false)


// =========================
// 生徒追加モーダル
// =========================

const showStudentModal = ref(false)

// 生徒追加モーダルを開く
const openStudentModal = () => {
    showStudentModal.value = true
    formError.value = ''
}

// =========================
// 生徒アカウント作成フォーム
// =========================

const studentForm = ref({
    name: '',
    email: '',
    password: ''
})

const creating = ref(false)
const formError = ref('')


// =========================
// 生徒一覧を取得
// =========================

const loadStudents = async () => {

    loading.value = true

    try {

        const response = await fetch(
            '/students-json',
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {
            throw new Error(
                '生徒一覧の取得に失敗しました'
            )
        }

        students.value =
            await response.json()

    } catch (error) {

        console.error(
            '生徒一覧取得エラー:',
            error
        )

        alert(
            '生徒一覧を取得できませんでした'
        )

    } finally {

        loading.value = false
    }
}

// =========================
// 生徒詳細
// =========================

const selectedStudent = ref(null)
const studentDetail = ref(null)
const detailLoading = ref(false)

const openStudent = async (student) => {
    selectedStudent.value = student
    detailLoading.value = true

    try {
        const response = await fetch(
            `/students-json/${student.id}`,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {
            throw new Error(
                '生徒詳細の取得に失敗しました'
            )
        }

        studentDetail.value =
            await response.json()

    } catch (error) {
        console.error(
            '生徒詳細取得エラー:',
            error
        )

        alert(
            '生徒詳細を取得できませんでした'
        )

        selectedStudent.value = null

    } finally {
        detailLoading.value = false
    }
}

const closeStudentDetail = () => {
    selectedStudent.value = null
    studentDetail.value = null
}

// =========================
// 日付表示
// =========================

const formatDateTime = (date) => {

    if (!date) {
        return ''
    }

    const value = new Date(date)

    if (Number.isNaN(value.getTime())) {
        return date
    }

    return value.toLocaleString(
        'ja-JP',
        {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        }
    )
}




// =========================
// 生徒追加モーダルを閉じる
// =========================

const closeStudentModal = () => {
    showStudentModal.value = false
    studentForm.value = {
        name: '',
        email: '',
        password: ''
    }
    formError.value = ''
}

// =========================
// 生徒アカウントを作成
// =========================

const createStudent = async () => {

    formError.value = ''

    if (!studentForm.value.name) {
        formError.value = '名前を入力してください'
        return
    }

    if (!studentForm.value.email) {
        formError.value = 'メールアドレスを入力してください'
        return
    }

    if (!studentForm.value.password) {
        formError.value = 'パスワードを入力してください'
        return
    }

    if (studentForm.value.password.length < 8) {
        formError.value =
            'パスワードは8文字以上で入力してください'
        return
    }

    creating.value = true

    try {

        const response = await fetch(
            '/students-json',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            ?.getAttribute('content')
                },

                body: JSON.stringify({
                    name: studentForm.value.name,
                    email: studentForm.value.email,
                    password: studentForm.value.password
                })
            }
        )

        const data = await response.json()
        if (!response.ok) {
            if (data.errors) {
                const firstError =
                    Object.values(data.errors)[0]
                formError.value =
                    firstError?.[0] ??
                    '入力内容を確認してください'
            } else {
                formError.value =
                    data.message ??
                    '生徒アカウントを作成できませんでした'
            }
            return
        }
        alert('生徒アカウントを作成しました！')
        closeStudentModal()
        await loadStudents()
    } catch (error) {
        console.error('生徒アカウント作成エラー:', error)
        formError.value =
            '生徒アカウントを作成できませんでした'
    } finally {
        creating.value = false

    }
}

// =========================
// 生徒アカウントを削除
// =========================
const deleteStudent = async () => {
    if (!selectedStudent.value) {
        return
    }

    const confirmed = window.confirm(
        `「${selectedStudent.value.name}」の生徒アカウントを削除しますか？\n\n所属クラス・提出履歴・回答データも削除されます。`
    )

    if (!confirmed) {
        return
    }

    try {
        const response = await fetch(
            `/students-json/${selectedStudent.value.id}`,
            {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector(
                                'meta[name="csrf-token"]'
                            )
                            ?.getAttribute('content')
                }
            }
        )

        const data = await response.json()

        if (!response.ok) {
            throw new Error(
                data.message ?? '削除に失敗しました'
            )
        }

        alert('生徒アカウントを削除しました')

        // 詳細画面を閉じる
        closeStudentDetail()

        // 生徒一覧を再取得
        await loadStudents()

    } catch (error) {
        console.error(
            '生徒アカウント削除エラー:',
            error
        )

        alert(
            '生徒アカウントを削除できませんでした'
        )
    }
}

onMounted(() => {

    loadStudents()

})
</script>


<style scoped>
/* =========================
   ページ
========================= */

.student-management-page {
    padding: 32px;
}


/* =========================
   ヘッダー
========================= */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.page-header h1 {
    margin: 0 0 8px;
}

.page-header p {
    margin: 0;
    color: #666;
}


/* =========================
   追加ボタン
========================= */

.add-button {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    background: #333;
    color: white;
    cursor: pointer;
}

.add-button:hover {
    opacity: 0.85;
}


/* =========================
   生徒一覧
========================= */

.student-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}


/* =========================
   生徒カード
========================= */

.student-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: white;
    cursor: pointer;
}

.student-card:hover {
    background: #f8f8f8;
}


/* =========================
   アイコン
========================= */

.student-icon {
    font-size: 28px;
}


/* =========================
   生徒情報
========================= */

.student-info {
    flex: 1;
}

.student-info h2 {
    margin: 0 0 6px;
    font-size: 18px;
}

.student-info p {
    margin: 0;
    color: #777;
}


/* =========================
   矢印
========================= */

.student-arrow {
    font-size: 22px;
    color: #999;
}


/* =========================
   空の場合
========================= */

.empty-message {
    padding: 40px;
    text-align: center;
    color: #777;
}


/* =========================
   ローディング
========================= */

.loading-message {
    padding: 40px;
    text-align: center;
    color: #777;
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
    background: rgba(0, 0, 0, 0.4);
    z-index: 1000;
}

.modal {
    width: 500px;
    max-width: 90%;
    padding: 24px;
    border-radius: 12px;
    background: white;
}


/* =========================
   モーダルヘッダー
========================= */

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
}

.close-button {
    border: none;
    background: transparent;
    font-size: 24px;
    cursor: pointer;
}


/* =========================
   説明
========================= */

.modal-description {
    color: #666;
    margin-bottom: 20px;
}


/* =========================
   モーダル内生徒一覧
========================= */

.modal-student-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.modal-student-item {
    display: flex;
    gap: 10px;
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    text-align: left;
}

.modal-student-item:hover {
    background: #f5f5f5;
}

/* =========================
   フォーム
========================= */

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

.form-group input:focus {
    outline: none;
    border-color: #999;
}


/* =========================
   フォームエラー
========================= */

.form-error {
    margin: 12px 0;
    color: #dc2626;
    font-size: 14px;
}


/* =========================
   作成ボタン
========================= */

.create-button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: #333;
    color: white;
    cursor: pointer;
    font-size: 14px;
}

.create-button:hover {
    opacity: 0.85;
}

.create-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.student-detail {
    max-width: 800px;
}

.back-button {
    margin-bottom: 24px;
    padding: 8px 14px;
    border: none;
    border-radius: 8px;
    background: #eee;
    cursor: pointer;
}

.back-button:hover {
    background: #ddd;
}

.detail-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px;
    margin-bottom: 24px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: white;
}

.detail-header h2 {
    margin: 0 0 6px;
}

.detail-header p {
    margin: 0;
    color: #777;
}

.detail-section {
    margin-bottom: 24px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: white;
}

.detail-section h3 {
    margin-top: 0;
}

.class-list,
.submission-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.no-data {
    margin: 0;
    color: #777;
}

/* =========================
   提出状況
========================= */

.submission-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.submission-item {
    padding: 18px;
    border: 1px solid #eee;
    border-radius: 10px;
    background: #fafafa;
}


/* =========================
   課題名・状態
========================= */

.submission-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.submission-title {
    font-size: 16px;
}


/* =========================
   提出状態
========================= */

.submission-status {
    display: flex;
    align-items: center;
}

.status {
    font-size: 14px;
}

.status.submitted {
    color: #15803d;
}

.status.returned {
    color: #7e22ce;
}

.status.draft {
    color: #777;
}


/* =========================
   提出情報
========================= */

.submission-detail {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #e5e5e5;
}

.submission-date {
    margin-bottom: 10px;
    color: #777;
    font-size: 13px;
}


/* =========================
   点数
========================= */

.submission-score {
    font-size: 18px;
    font-weight: bold;
}

.submission-score.no-score {
    color: #999;
    font-size: 14px;
    font-weight: normal;
}


/* =========================
   コメント
========================= */

.submission-comment {
    margin-top: 14px;
    padding: 12px;
    border-radius: 8px;
    background: white;
}

.comment-label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
    color: #666;
}

.submission-comment p {
    margin: 0;
    line-height: 1.6;
    white-space: pre-wrap;
}

/* =========================
   生徒アカウント削除
========================= */

.student-detail-info {
    flex: 1;
}

.delete-student-button {
    padding: 10px 14px;
    border: none;
    border-radius: 8px;
    background: #dc2626;
    color: white;
    cursor: pointer;
    font-size: 14px;
}

.delete-student-button:hover {
    opacity: 0.85;
}

/* =========================
   所属クラス
========================= */

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.section-header h3 {
    margin: 0;
}

.add-class-button {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    background: #333;
    color: white;
    cursor: pointer;
    font-size: 13px;
}

.add-class-button:hover {
    opacity: 0.85;
}

.class-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border: 1px solid #eee;
    border-radius: 8px;
    background: #fafafa;
}

.remove-class-button {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 6px;
    background: #eee;
    color: #666;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
}

.remove-class-button:hover {
    background: #ddd;
    color: #333;
}
</style>