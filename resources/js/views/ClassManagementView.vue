<template>

    <div class="class-page">

        <!-- =================================================
             クラス詳細画面
        ================================================== -->

        <template v-if="selectedClass">

            <div class="page-header">

                <div>

                    <button class="back-link" @click="backToClasses">
                        ← クラス一覧
                    </button>

                    <h1>
                        🏫 {{ selectedClass.name }}
                    </h1>

                    <p>
                        {{ selectedClass.grade }}年
                    </p>

                </div>

            </div>


            <!-- =========================
                 生徒一覧
            ========================== -->

            <section class="student-section">

                <div class="section-header">

                    <div>

                        <h2>
                            👤 生徒一覧
                        </h2>

                        <p>
                            {{ selectedClass.users.length }}人
                        </p>

                    </div>


                    <button class="add-button" @click="openStudentModal">
                        ＋ 生徒を追加
                    </button>

                </div>


                <!-- 生徒がいる場合 -->

                <div v-if="selectedClass.users.length > 0" class="student-list">

                    <div v-for="student in selectedClass.users" :key="student.id" class="student-card">

                        <div class="student-icon">
                            👤
                        </div>


                        <div class="student-info">

                            <h3>
                                {{ student.name }}
                            </h3>

                            <p>
                                {{ student.email }}
                            </p>

                        </div>

                    </div>

                </div>


                <!-- 生徒がいない場合 -->

                <div v-else class="empty-message">
                    このクラスにはまだ生徒がいません。
                </div>

            </section>

            <!-- =================================================
     生徒追加モーダル
================================================== -->

            <div v-if="showStudentModal" class="modal-overlay" @click.self="closeStudentModal">

                <div class="student-modal">

                    <div class="modal-header">

                        <div>

                            <h2>
                                生徒を追加
                            </h2>

                            <p>
                                {{ selectedClass.name }} に追加する生徒を選択してください
                            </p>

                        </div>

                        <button class="close-button" @click="closeStudentModal">
                            ×
                        </button>

                    </div>


                    <!-- 読み込み中 -->

                    <div v-if="studentsLoading" class="modal-message">
                        生徒一覧を読み込み中……
                    </div>


                    <!-- 生徒一覧 -->

                    <div v-else-if="students.length > 0" class="modal-student-list">

                        <div v-for="student in students" :key="student.id" class="modal-student-card">

                            <div class="student-icon">
                                👤
                            </div>


                            <div class="student-info">

                                <h3>
                                    {{ student.name }}
                                </h3>

                                <p>
                                    {{ student.email }}
                                </p>

                            </div>


                            <button class="select-button" :class="{ 'already-added': isStudentInClass(student) }"
                                :disabled="isStudentInClass(student)" @click="addStudent(student)">
                                {{ isStudentInClass(student) ? '所属済み' : '追加' }}
                            </button>

                        </div>

                    </div>


                    <!-- 生徒がいない -->

                    <div v-else class="modal-message">
                        登録されている生徒がいません。
                    </div>

                </div>

            </div>
        </template>


        <!-- =================================================
             クラス一覧画面
        ================================================== -->

        <template v-else>

            <div class="page-header">

                <div>

                    <h1>
                        クラス管理
                    </h1>

                    <p>
                        クラスと生徒を管理します
                    </p>

                </div>


                <button class="back-button" @click="$emit('back')">
                    ← 教材一覧へ戻る
                </button>

            </div>


            <section class="class-section">

                <div class="section-header">

                    <h2>
                        クラス一覧
                    </h2>


                    <button class="add-button" @click="openClassModal">
                        ＋ クラスを追加
                    </button>

                </div>


                <!-- 読み込み中 -->

                <div v-if="loading" class="empty-message">
                    クラス一覧を読み込み中です……
                </div>


                <!-- エラー -->

                <div v-else-if="error" class="error-message">
                    {{ error }}
                </div>


                <!-- クラスがない -->

                <div v-else-if="classes.length === 0" class="empty-message">
                    クラスがありません。
                </div>


                <!-- クラス一覧 -->

                <div v-else class="class-list">

                    <div v-for="schoolClass in classes" :key="schoolClass.id" class="class-card"
                        @click="openClass(schoolClass)">

                        <div class="class-icon">
                            🏫
                        </div>


                        <div class="class-info">

                            <h3>
                                {{ schoolClass.name }}
                            </h3>

                            <p>
                                学年：
                                {{ schoolClass.grade }}年
                            </p>

                            <p>
                                生徒：
                                {{ schoolClass.users_count }}人
                            </p>

                            <span class="detail-text">
                                クリックして詳細を見る →
                            </span>

                        </div>

                    </div>

                </div>

            </section>

            <!-- =================================================
     クラス追加モーダル
================================================== -->

            <div v-if="showClassModal" class="modal-overlay" @click.self="closeClassModal">

                <div class="student-modal">

                    <div class="modal-header">

                        <div>

                            <h2>
                                クラスを追加
                            </h2>

                            <p>
                                新しいクラスを作成します
                            </p>

                        </div>

                        <button class="close-button" @click="closeClassModal">
                            ×
                        </button>

                    </div>


                    <!-- クラス名 -->

                    <div class="form-group">

                        <label>
                            クラス名
                        </label>

                        <input v-model="newClass.name" type="text" placeholder="例：1年A組">

                    </div>


                    <!-- 学年 -->

                    <div class="form-group">

                        <label>
                            学年
                        </label>

                        <select v-model="newClass.grade">

                            <option value="">
                                選択してください
                            </option>

                            <option v-for="grade in 6" :key="grade" :value="grade">
                                {{ grade }}年
                            </option>

                        </select>

                    </div>


                    <!-- エラー -->

                    <div v-if="classCreateError" class="form-error">
                        {{ classCreateError }}
                    </div>


                    <!-- ボタン -->

                    <div class="modal-actions">

                        <button class="cancel-button" @click="closeClassModal">
                            キャンセル
                        </button>

                        <button class="create-button" :disabled="classCreating" @click="createClass">
                            {{ classCreating ? '作成中……' : 'クラスを作成' }}
                        </button>

                    </div>

                </div>

            </div>

        </template>


        <!-- =================================================
             詳細読み込み中
        ================================================== -->

        <div v-if="classLoading" class="loading-overlay">
            クラス情報を読み込み中……
        </div>

    </div>

</template>


<script setup>

import { ref, onMounted } from 'vue'

defineEmits([
    'back'
])


// =========================
// クラス一覧
// =========================

const classes = ref([])

const loading = ref(true)

const error = ref(null)


// =========================
// 選択中のクラス
// =========================

const selectedClass = ref(null)

const classLoading = ref(false)


// =========================
// 生徒一覧
// =========================

const students = ref([])

const studentsLoading = ref(false)


// =========================
// 生徒追加画面
// =========================

const showStudentModal = ref(false)

// =========================
// クラス追加
// =========================

const showClassModal = ref(false)
const classCreating = ref(false)
const classCreateError = ref(null)
const newClass = ref({
    name: '',
    grade: ''
})


// =========================
// クラス一覧取得
// =========================

const loadClasses = async () => {

    try {

        loading.value = true

        const response = await fetch(
            '/classes-json',
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {
            throw new Error(
                'クラスの取得に失敗しました'
            )
        }

        classes.value =
            await response.json()

    } catch (err) {

        console.error(
            'クラス取得エラー:',
            err
        )

        error.value =
            'クラスの取得に失敗しました'

    } finally {
        loading.value = false
    }
}
// =========================
// クラス詳細取得
// =========================

const openClass = async (schoolClass) => {

    try {

        classLoading.value = true

        const response = await fetch(
            `/classes-json/${schoolClass.id}`,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {
            throw new Error(
                'クラス詳細の取得に失敗しました'
            )
        }

        selectedClass.value =
            await response.json()

    } catch (err) {

        console.error(
            'クラス詳細取得エラー:',
            err
        )
    } finally {
        classLoading.value = false
    }
}


// =========================
// 生徒一覧取得
// =========================

const loadStudents = async () => {

    try {

        studentsLoading.value = true

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
    } catch (err) {
        console.error(
            '生徒取得エラー:',
            err
        )
    } finally {
        studentsLoading.value = false
    }
}


// =========================
// 生徒追加画面を開く
// =========================

const openStudentModal = async () => {
    await loadStudents()
    showStudentModal.value = true
}

// =========================
// 生徒をクラスに追加
// =========================

const addStudent = async (student) => {

    try {

        const response = await fetch(
            `/classes-json/${selectedClass.value.id}/students`,
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
                    user_id: student.id
                })
            }
        )

        const data =
            await response.json()

        if (!response.ok) {

            alert(
                data.message ??
                '生徒の追加に失敗しました'
            )

            return
        }

        alert('生徒を追加しました！')

        // クラス情報を再取得
        await openClass(
            selectedClass.value
        )

        // モーダルを閉じる
        closeStudentModal()

    } catch (err) {

        console.error(
            '生徒追加エラー:',
            err
        )

        alert(
            '生徒の追加に失敗しました'
        )
    }

}

// =========================
// クラス追加画面を開く
// =========================

const openClassModal = () => {

    newClass.value = {
        name: '',
        grade: ''
    }

    classCreateError.value = null

    showClassModal.value = true
}


// =========================
// クラス追加画面を閉じる
// =========================

const closeClassModal = () => {

    showClassModal.value = false

}


// =========================
// クラスを作成
// =========================

const createClass = async () => {

    classCreateError.value = null

    if (!newClass.value.name.trim()) {

        classCreateError.value =
            'クラス名を入力してください'

        return
    }

    if (!newClass.value.grade) {

        classCreateError.value =
            '学年を選択してください'

        return
    }


    try {

        classCreating.value = true

        const response = await fetch(
            '/classes-json',
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
                    name: newClass.value.name,
                    grade: Number(
                        newClass.value.grade
                    )
                })
            }
        )

        const data =
            await response.json()
        if (!response.ok) {
            classCreateError.value =
                data.message ??
                'クラスの作成に失敗しました'
            return
        }
        // クラス一覧を再取得
        await loadClasses()
        // モーダルを閉じる
        closeClassModal()
        alert('クラスを作成しました！')

    } catch (err) {
        console.error(
            'クラス作成エラー:',
            err
        )
        classCreateError.value =
            'クラスの作成に失敗しました'
    } finally {
        classCreating.value = false
    }

}

// =========================
// 生徒がすでに所属しているか
// =========================

const isStudentInClass = (student) => {

    return selectedClass.value?.users?.some(
        user => user.id === student.id
    )
}


// =========================
// 生徒追加画面を閉じる
// =========================

const closeStudentModal = () => {
    showStudentModal.value = false
}


// =========================
// クラス一覧へ戻る
// =========================

const backToClasses = () => {
    selectedClass.value = null
}


// =========================
// 初期読み込み
// =========================

onMounted(() => {
    loadClasses()
})
</script>


<style scoped>
.class-page {
    padding: 30px;
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

h1 {
    margin: 0 0 8px;
    font-size: 28px;
}

.page-header p {
    margin: 0;
    color: #666;
}

/* =========================
   戻るボタン
========================= */

.back-button {
    padding: 10px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: white;
    cursor: pointer;
}

.back-button:hover {
    background: #f3f4f6;
}

/* =========================
   クラス一覧
========================= */

.class-section {
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h2 {
    margin: 0;
}

/* =========================
   クラス追加
========================= */

.add-button {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    background: #3b82f6;
    color: white;
    cursor: pointer;
}

.add-button:hover {
    background: #2563eb;
}

/* =========================
   読み込み中
========================= */

.empty-message {
    padding: 30px;
    text-align: center;
    color: #888;
    background: #f8fafc;
    border-radius: 8px;
}

/* =========================
   クラス一覧
========================= */

.class-list {
    display: grid;
    grid-template-columns:
        repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}

/* クラスカード */

.class-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: white;
    transition: 0.2s;
}

.class-card:hover {
    border-color: #93c5fd;
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.06);
}


/* クラスアイコン */

.class-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #eff6ff;
    font-size: 24px;
}


/* クラス情報 */

.class-info h3 {
    margin: 0 0 8px;
    font-size: 18px;
}


.class-info p {
    margin: 3px 0;
    color: #666;
    font-size: 14px;
}


/* エラー */

.error-message {
    padding: 30px;
    text-align: center;
    color: #dc2626;
    background: #fef2f2;
    border-radius: 8px;
}

/* =========================
   戻るリンク
========================= */

.back-link {
    margin-bottom: 15px;
    padding: 0;
    border: none;
    background: transparent;
    color: #2563eb;
    font-size: 14px;
    cursor: pointer;
}

.back-link:hover {
    text-decoration: underline;
}


/* =========================
   クラスカード
========================= */

.class-card {
    cursor: pointer;
}

.class-card:hover {
    border-color: #93c5fd;
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.08);
}

/* 詳細を見る */

.detail-text {
    display: block;
    margin-top: 10px;
    color: #2563eb;
    font-size: 13px;
}


/* =========================
   生徒一覧
========================= */

.student-section {
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 24px;
}


.student-section .section-header {
    margin-bottom: 20px;
}


.student-section .section-header h2 {
    margin: 0 0 5px;
}


.student-section .section-header p {

    margin: 0;
    color: #888;
    font-size: 14px;
}

/* =========================
   生徒カード
========================= */

.student-list {
    display: grid;
    grid-template-columns:
        repeat(auto-fill,
            minmax(260px, 1fr));
    gap: 16px;
}


.student-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 18px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: #fafafa;
}


.student-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #eff6ff;
    font-size: 22px;
}

.student-info h3 {
    margin: 0 0 5px;
    font-size: 16px;
}


.student-info p {
    margin: 0;
    color: #777;
    font-size: 13px;
}


/* =========================
   詳細読み込み中
========================= */

.loading-overlay {
    position: fixed;
    bottom: 30px;
    right: 30px;
    padding: 12px 18px;
    border-radius: 8px;
    background: #333;
    color: white;
    font-size: 14px;
}

/* =========================
   モーダル背景
========================= */

.modal-overlay {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background:
        rgba(0, 0, 0, 0.4);
    z-index: 1000;
}


/* =========================
   モーダル
========================= */

.student-modal {
    width: 100%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    padding: 25px;
    box-sizing: border-box;
    border-radius: 16px;
    background: white;
    box-shadow:
        0 10px 40px rgba(0, 0, 0, 0.2);
}


/* =========================
   モーダルヘッダー
========================= */

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
}


.modal-header h2 {
    margin: 0 0 8px;
}


.modal-header p {
    margin: 0;
    color: #777;
    font-size: 14px;
}

.close-button {
    width: 36px;
    height: 36px;
    padding: 0;
    border-radius: 50%;
    background: #f3f4f6;
    color: #555;
    font-size: 24px;
    line-height: 1;
}

.close-button:hover {
    background: #e5e7eb;
}

/* =========================
   生徒選択一覧
========================= */

.modal-student-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.modal-student-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 14px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: #fafafa;
}

.modal-student-card .student-info {
    flex: 1;
}


.select-button {
    padding: 8px 16px;
    background: #3b82f6;
}


/* =========================
   メッセージ
========================= */

.modal-message {
    padding: 40px 20px;
    text-align: center;
    color: #777;
}

/* =========================
   所属済みボタン
========================= */

.select-button.already-added {
    background: #e5e7eb;
    color: #888;
    cursor: not-allowed;
}

.select-button.already-added:hover {
    background: #e5e7eb;
}

/* =========================
   フォーム
========================= */

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    font-size: 14px;
}

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    background: white;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
}


/* =========================
   フォームエラー
========================= */

.form-error {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 8px;
    background: #fef2f2;
    color: #dc2626;
    font-size: 14px;
}


/* =========================
   モーダルボタン
========================= */

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 25px;
}

.cancel-button,
.create-button {
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
}

.cancel-button {
    border: 1px solid #ddd;
    background: white;
    color: #555;
}

.cancel-button:hover {
    background: #f3f4f6;
}

.create-button {
    border: none;
    background: #3b82f6;
    color: white;
}

.create-button:hover {
    background: #2563eb;
}

.create-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
