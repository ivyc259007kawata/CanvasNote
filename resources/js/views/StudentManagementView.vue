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
        <section class="student-section">

            <div v-if="loading" class="loading-message">
                生徒情報を読み込んでいます...
            </div>


            <div v-else-if="students.length > 0" class="student-list">

                <div v-for="student in students" :key="student.id" class="student-card" @click="openStudent(student)">

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
// 生徒追加モーダルを開く
// =========================

const openStudentModal = () => {

    showStudentModal.value = true

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
// 生徒詳細を開く
// =========================

const openStudent = (student) => {
    console.log('選択した生徒:', student)
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
</style>