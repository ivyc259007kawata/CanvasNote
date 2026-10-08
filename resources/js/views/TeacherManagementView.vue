<template>
    <div class="teacher-page">

        <!-- =========================
             ヘッダー
        ========================== -->
        <div class="page-header">
            <div>
                <h1>👨‍🏫 先生</h1>
                <p>先生アカウントを管理します。</p>
            </div>

            <button class="add-button" @click="openTeacherModal">
                ＋ 先生を追加
            </button>
        </div>


        <!-- =========================
             先生一覧
        ========================== -->
        <div v-if="teachers.length > 0" class="teacher-list">

            <div v-for="teacher in teachers" :key="teacher.id" class="teacher-card">
                <div class="teacher-icon">
                    👨‍🏫
                </div>

                <div class="teacher-info">
                    <h2>{{ teacher.name }}</h2>

                    <p>
                        <span>ログインID：</span>
                        {{ teacher.login_id }}
                    </p>

                    <p>
                        <span>メール：</span>
                        {{ teacher.email }}
                    </p>
                </div>

                <button class="detail-button" @click="openTeacher(teacher)">
                    詳細
                </button>
            </div>

        </div>


        <!-- =========================
             先生がいない場合
        ========================== -->
        <div v-else class="empty-state">
            <div class="empty-icon">
                👨‍🏫
            </div>

            <h2>先生管理</h2>

            <p>
                先生アカウントを追加すると、
                ここに表示されます。
            </p>
        </div>


        <!-- =========================
             先生詳細
        ========================== -->
        <div v-if="showTeacherDetail" class="modal-overlay" @click.self="closeTeacherDetail">
            <div class="modal detail-modal">

                <div class="modal-header">
                    <h2>👨‍🏫 先生詳細</h2>

                    <button class="close-button" @click="closeTeacherDetail">
                        ×
                    </button>
                </div>

                <div v-if="selectedTeacher">

                    <!-- 基本情報 -->
                    <div class="detail-section">

                        <h3>基本情報</h3>

                        <div class="detail-item">
                            <span>名前</span>

                            <strong>
                                {{ selectedTeacher.name }}
                            </strong>
                        </div>

                        <div class="detail-item">
                            <span>ログインID</span>

                            <strong>
                                {{ selectedTeacher.login_id }}
                            </strong>
                        </div>

                        <div class="detail-item">
                            <span>メールアドレス</span>

                            <strong>
                                {{ selectedTeacher.email }}
                            </strong>
                        </div>

                    </div>


                    <!-- 担当クラス -->
                    <div class="detail-section">

                        <div class="section-title-row">

                            <h3>担当クラス</h3>

                            <button class="add-class-button" @click="openClassModal">
                                ＋ 設定
                            </button>

                        </div>


                        <div v-if="teacherClasses.length > 0" class="class-list">

                            <div v-for="schoolClass in teacherClasses" :key="schoolClass.id" class="class-item">

                                <div class="class-item-info">
                                    <strong>
                                        {{ schoolClass.name }}
                                    </strong>

                                    <span>
                                        {{ schoolClass.grade }}年
                                    </span>
                                </div>

                                <button class="remove-class-button" :disabled="removingClassId === schoolClass.id
                                    " @click="
                                        removeTeacherClass(schoolClass)
                                        ">
                                    {{
                                        removingClassId === schoolClass.id
                                            ? '解除中...'
                                            : '解除'
                                    }}
                                </button>

                            </div>

                        </div>

                        <p v-else class="no-data">
                            担当しているクラスはありません。
                        </p>

                    </div>


                    <!-- 削除 -->
                    <div class="delete-section">

                        <button class="delete-button" :disabled="deletingTeacher" @click="deleteTeacher">
                            {{
                                deletingTeacher
                                    ? '削除中...'
                                    : 'この先生アカウントを削除'
                            }}
                        </button>

                    </div>

                </div>

            </div>
        </div>


        <!-- =========================
             担当クラス設定モーダル
        ========================== -->
        <div v-if="showClassModal" class="modal-overlay" @click.self="closeClassModal">
            <div class="modal class-modal">

                <div class="modal-header">
                    <h2>＋ 担当クラスを設定</h2>

                    <button class="close-button" @click="closeClassModal">
                        ×
                    </button>
                </div>

                <p class="modal-description">
                    {{ selectedTeacher?.name }}先生の担当クラスを設定します。
                </p>


                <!-- クラス取得中 -->
                <div v-if="loadingClasses" class="class-loading">
                    クラスを読み込んでいます...
                </div>


                <!-- クラスがない -->
                <div v-else-if="availableClasses.length === 0" class="no-class-message">
                    登録されているクラスがありません。
                </div>


                <!-- クラス一覧 -->
                <div v-else class="select-class-list">

                    <button v-for="schoolClass in availableClasses" :key="schoolClass.id" class="select-class-item"
                        :disabled="assigningClassId === schoolClass.id" @click="assignTeacherClass(schoolClass)">

                        <div>
                            <strong>
                                {{ schoolClass.name }}
                            </strong>

                            <span>
                                {{ schoolClass.grade }}年
                            </span>
                        </div>

                        <span class="select-class-action">
                            {{
                                assigningClassId === schoolClass.id
                                    ? '設定中...'
                                    : '設定'
                            }}
                        </span>

                    </button>

                </div>

            </div>
        </div>


        <!-- =========================
             先生アカウント作成モーダル
        ========================== -->
        <div v-if="showTeacherModal" class="modal-overlay" @click.self="closeTeacherModal">
            <div class="modal">

                <div class="modal-header">
                    <h2>＋ 先生アカウントを作成</h2>

                    <button class="close-button" @click="closeTeacherModal">
                        ×
                    </button>
                </div>

                <p class="modal-description">
                    新しい先生のアカウントを作成します。
                </p>


                <!-- 名前 -->
                <div class="form-group">
                    <label>名前</label>

                    <input v-model="teacherForm.name" type="text" placeholder="例：山田先生">
                </div>


                <!-- ログインID -->
                <div class="form-group">
                    <label>ログインID</label>

                    <input v-model="teacherForm.login_id" type="text" placeholder="例：teacher03">
                </div>


                <!-- メール -->
                <div class="form-group">
                    <label>メールアドレス</label>

                    <input v-model="teacherForm.email" type="email" placeholder="例：yamada@example.com">
                </div>


                <!-- パスワード -->
                <div class="form-group">
                    <label>パスワード</label>

                    <input v-model="teacherForm.password" type="password" placeholder="8文字以上">
                </div>


                <!-- エラー -->
                <p v-if="teacherFormError" class="form-error">
                    {{ teacherFormError }}
                </p>


                <!-- 作成ボタン -->
                <button class="create-button" :disabled="creatingTeacher" @click="createTeacher">
                    {{
                        creatingTeacher
                            ? '作成中...'
                            : '先生アカウントを作成'
                    }}
                </button>

            </div>
        </div>

    </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'


// =========================
// 先生一覧
// =========================
const teachers = ref([])


// =========================
// 先生詳細
// =========================
const showTeacherDetail = ref(false)
const selectedTeacher = ref(null)
const teacherClasses = ref([])
const deletingTeacher = ref(false)


// =========================
// 担当クラス設定
// =========================
const showClassModal = ref(false)
const availableClasses = ref([])
const loadingClasses = ref(false)
const assigningClassId = ref(null)
const removingClassId = ref(null)


// =========================
// 先生作成モーダル
// =========================
const showTeacherModal = ref(false)


// =========================
// 先生フォーム
// =========================
const teacherForm = ref({
    name: '',
    login_id: '',
    email: '',
    password: ''
})


// =========================
// 作成状態
// =========================
const creatingTeacher = ref(false)


// =========================
// エラー
// =========================
const teacherFormError = ref('')


// =========================
// CSRFトークン
// =========================
const csrfToken =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content')


// =========================
// 先生追加モーダルを開く
// =========================
const openTeacherModal = () => {
    teacherFormError.value = ''

    showTeacherModal.value = true
}


// =========================
// 先生追加モーダルを閉じる
// =========================
const closeTeacherModal = () => {
    showTeacherModal.value = false

    teacherForm.value = {
        name: '',
        login_id: '',
        email: '',
        password: ''
    }

    teacherFormError.value = ''
}


// =========================
// 先生一覧を取得
// =========================
const loadTeachers = async () => {

    try {

        const response = await fetch(
            '/teachers-json',
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {
            console.error(
                '先生一覧の取得に失敗しました'
            )

            return
        }

        const data = await response.json()

        teachers.value =
            data.teachers ?? []

    } catch (error) {

        console.error(
            '先生一覧取得エラー:',
            error
        )
    }
}


// =========================
// 先生詳細を開く
// =========================
const openTeacher = async (teacher) => {

    try {

        const response = await fetch(
            `/teachers-json/${teacher.id}`,
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {

            alert(
                '先生情報を取得できませんでした。'
            )

            return
        }

        const data =
            await response.json()

        selectedTeacher.value =
            data.teacher

        teacherClasses.value =
            data.classes ?? []

        showTeacherDetail.value = true

    } catch (error) {

        console.error(
            '先生詳細取得エラー:',
            error
        )

        alert(
            '先生情報を取得できませんでした。'
        )
    }
}


// =========================
// 先生詳細を閉じる
// =========================
const closeTeacherDetail = () => {

    showTeacherDetail.value =
        false

    selectedTeacher.value =
        null

    teacherClasses.value =
        []

    showClassModal.value =
        false

    availableClasses.value =
        []
}


// =========================
// 担当クラス設定モーダルを開く
// =========================
const openClassModal = async () => {

    showClassModal.value = true

    await loadAvailableClasses()
}


// =========================
// 担当クラス設定モーダルを閉じる
// =========================
const closeClassModal = () => {

    showClassModal.value =
        false

    availableClasses.value =
        []

    assigningClassId.value =
        null
}


// =========================
// クラス一覧を取得
// =========================
const loadAvailableClasses = async () => {

    loadingClasses.value = true

    try {

        const response = await fetch(
            '/teachers-json/classes',
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (!response.ok) {

            alert(
                'クラス一覧を取得できませんでした。'
            )

            return
        }

        const data =
            await response.json()

        availableClasses.value =
            data.classes ?? []

    } catch (error) {

        console.error(
            'クラス一覧取得エラー:',
            error
        )

        alert(
            'クラス一覧を取得できませんでした。'
        )

    } finally {

        loadingClasses.value =
            false
    }
}


// =========================
// 担当クラスを設定
// =========================
const assignTeacherClass = async (schoolClass) => {

    if (!selectedTeacher.value) {
        return
    }

    assigningClassId.value =
        schoolClass.id

    try {

        const response = await fetch(
            `/teachers-json/${selectedTeacher.value.id}/classes`,
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },

                body: JSON.stringify({
                    class_id: schoolClass.id
                })
            }
        )

        const data =
            await response.json()

        if (!response.ok) {

            alert(
                data.message ??
                '担当クラスを設定できませんでした。'
            )

            return
        }

        // 設定成功後、先生詳細を再取得
        await openTeacher(
            selectedTeacher.value
        )

        // クラス設定モーダルを閉じる
        closeClassModal()

    } catch (error) {

        console.error(
            '担当クラス設定エラー:',
            error
        )

        alert(
            '担当クラスを設定できませんでした。'
        )

    } finally {

        assigningClassId.value =
            null
    }
}


// =========================
// 担当クラスを解除
// =========================
const removeTeacherClass = async (schoolClass) => {

    if (!selectedTeacher.value) {
        return
    }

    const confirmed = confirm(
        `${schoolClass.name}を担当クラスから外しますか？`
    )

    if (!confirmed) {
        return
    }

    removingClassId.value =
        schoolClass.id

    try {

        const response = await fetch(
            `/teachers-json/${selectedTeacher.value.id}/classes/${schoolClass.id}`,
            {
                method: 'DELETE',

                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            }
        )

        const data =
            await response.json()

        if (!response.ok) {

            alert(
                data.message ??
                '担当クラスを解除できませんでした。'
            )

            return
        }

        // 先生詳細を再取得
        await openTeacher(
            selectedTeacher.value
        )

    } catch (error) {

        console.error(
            '担当クラス解除エラー:',
            error
        )

        alert(
            '担当クラスを解除できませんでした。'
        )

    } finally {

        removingClassId.value =
            null
    }
}


// =========================
// 先生アカウント削除
// =========================
const deleteTeacher = async () => {

    if (!selectedTeacher.value) {
        return
    }

    const teacherName =
        selectedTeacher.value.name

    const confirmed = confirm(
        `${teacherName}さんのアカウントを削除しますか？\n\nこの操作は元に戻せません。`
    )

    if (!confirmed) {
        return
    }

    deletingTeacher.value = true

    try {

        const response = await fetch(
            `/teachers-json/${selectedTeacher.value.id}`,
            {
                method: 'DELETE',

                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            }
        )

        const data =
            await response.json()

        if (!response.ok) {

            alert(
                data.message ??
                '先生アカウントを削除できませんでした。'
            )

            return
        }

        alert(
            '先生アカウントを削除しました。'
        )

        closeTeacherDetail()

        await loadTeachers()

    } catch (error) {

        console.error(
            '先生アカウント削除エラー:',
            error
        )

        alert(
            '先生アカウントを削除できませんでした。'
        )

    } finally {

        deletingTeacher.value = false
    }
}


// =========================
// 先生アカウント作成
// =========================
const createTeacher = async () => {

    teacherFormError.value = ''


    // 名前チェック
    if (!teacherForm.value.name) {

        teacherFormError.value =
            '名前を入力してください'

        return
    }


    // ログインIDチェック
    if (!teacherForm.value.login_id) {

        teacherFormError.value =
            'ログインIDを入力してください'

        return
    }


    // メールチェック
    if (!teacherForm.value.email) {

        teacherFormError.value =
            'メールアドレスを入力してください'

        return
    }


    // パスワードチェック
    if (!teacherForm.value.password) {

        teacherFormError.value =
            'パスワードを入力してください'

        return
    }


    // パスワード文字数
    if (teacherForm.value.password.length < 8) {

        teacherFormError.value =
            'パスワードは8文字以上で入力してください'

        return
    }


    creatingTeacher.value = true


    try {

        const response = await fetch(
            '/teachers-json',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },

                body: JSON.stringify({
                    name:
                        teacherForm.value.name,

                    login_id:
                        teacherForm.value.login_id,

                    email:
                        teacherForm.value.email,

                    password:
                        teacherForm.value.password
                })
            }
        )


        const data =
            await response.json()


        // =========================
        // エラー処理
        // =========================
        if (!response.ok) {

            // ログインID重複
            if (data.errors?.login_id) {

                alert(
                    'ログインIDが重複しているため登録できません。'
                )

                return
            }


            // メール重複
            if (data.errors?.email) {

                alert(
                    'メールアドレスが重複しているため登録できません。'
                )

                return
            }


            // 名前
            if (data.errors?.name) {

                teacherFormError.value =
                    data.errors.name[0]

                return
            }


            // パスワード
            if (data.errors?.password) {

                teacherFormError.value =
                    data.errors.password[0]

                return
            }


            teacherFormError.value =
                data.message ??
                '入力内容を確認してください'

            return
        }


        // =========================
        // 成功
        // =========================
        alert(
            '先生アカウントを作成しました！'
        )


        closeTeacherModal()

        await loadTeachers()

    } catch (error) {

        console.error(
            '先生アカウント作成エラー:',
            error
        )

        teacherFormError.value =
            '先生アカウントを作成できませんでした'

    } finally {

        creatingTeacher.value =
            false
    }
}


// =========================
// 初期表示
// =========================
onMounted(() => {

    loadTeachers()

})
</script>


<style scoped>
.teacher-page {
    min-height: 100%;
    padding: 30px;
    box-sizing: border-box;
}


/* =========================
   ヘッダー
========================= */

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0 0 8px;
    font-size: 28px;
    color: #222;
}

.page-header p {
    margin: 0;
    color: #777;
}


/* =========================
   追加ボタン
========================= */

.add-button {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    font-size: 14px;
}

.add-button:hover {
    background: #1d4ed8;
}


/* =========================
   先生一覧
========================= */

.teacher-list {
    display: grid;
    grid-template-columns:
        repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.teacher-card {
    display: flex;
    align-items: center;
    gap: 16px;

    padding: 20px;

    background: white;

    border: 1px solid #e5e7eb;
    border-radius: 12px;

    box-sizing: border-box;
}

.teacher-icon {
    width: 52px;
    height: 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: #e0edff;

    border-radius: 50%;

    font-size: 26px;
}

.teacher-info {
    min-width: 0;
}

.teacher-info h2 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #333;
}

.teacher-info p {
    margin: 4px 0;
    font-size: 13px;
    color: #666;
    word-break: break-all;
}

.teacher-info p span {
    font-weight: 600;
    color: #444;
}


/* =========================
   空状態
========================= */

.empty-state {
    min-height: 300px;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    background: white;

    border-radius: 12px;

    border: 1px solid #e5e7eb;
}

.empty-icon {
    font-size: 56px;
    margin-bottom: 15px;
}

.empty-state h2 {
    margin: 0 0 10px;
    color: #444;
}

.empty-state p {
    margin: 0;
    color: #888;
}


/* =========================
   モーダル
========================= */

.modal {
    width: 420px;
    max-width: calc(100% - 40px);

    padding: 24px;

    background: white;

    border-radius: 12px;

    box-sizing: border-box;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.15);
}


/* =========================
   モーダルヘッダー
========================= */

.modal-header {
    margin-bottom: 10px;
}

.modal-header h2 {
    font-size: 20px;
    color: #333;
}

.close-button {
    width: 32px;
    height: 32px;

    border-radius: 6px;

    font-size: 24px;
    color: #777;
}

.close-button:hover {
    background: #f1f5f9;
}


/* =========================
   説明
========================= */

.modal-description {
    margin: 0 0 20px;

    color: #777;
    font-size: 14px;
}


/* =========================
   フォーム
========================= */

.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: #444;
}

.form-group input {
    width: 100%;

    padding: 10px 12px;

    border: 1px solid #d1d5db;
    border-radius: 8px;

    box-sizing: border-box;

    font-size: 14px;
}

.form-group input:focus {
    outline: none;

    border-color: #2563eb;

    box-shadow:
        0 0 0 2px rgba(37, 99, 235, 0.1);
}


/* =========================
   作成ボタン
========================= */

.create-button {
    width: 100%;

    padding: 12px;

    border: none;
    border-radius: 8px;

    background: #2563eb;

    color: white;

    font-size: 14px;
    font-weight: 600;
}

.create-button:hover {
    background: #1d4ed8;
}

.create-button:disabled {
    background: #93c5fd;
    cursor: not-allowed;
}


/* =========================
   詳細ボタン
========================= */

.detail-button {
    margin-left: auto;

    padding: 8px 14px;

    border: none;
    border-radius: 7px;

    background: #e0edff;
    color: #2563eb;

    font-size: 13px;
    font-weight: 600;
}

.detail-button:hover {
    background: #d2e4ff;
}


/* =========================
   詳細モーダル
========================= */

.detail-modal {
    max-height: 80vh;
    overflow-y: auto;
}


/* =========================
   詳細セクション
========================= */

.detail-section {
    margin-bottom: 24px;
}

.detail-section h3 {
    margin: 0;

    font-size: 15px;
    color: #444;
}

.section-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 12px;
}

.add-class-button {
    padding: 6px 10px;

    border: none;
    border-radius: 6px;

    background: #e0edff;
    color: #2563eb;

    font-size: 12px;
    font-weight: 600;
}

.add-class-button:hover {
    background: #d2e4ff;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    gap: 20px;

    padding: 12px 0;

    border-bottom: 1px solid #eee;

    font-size: 14px;
}

.detail-item span {
    color: #777;
}

.detail-item strong {
    color: #333;
    text-align: right;
    word-break: break-all;
}


/* =========================
   クラス一覧
========================= */

.class-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.class-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;

    padding: 10px 12px;

    background: #f8fafc;

    border: 1px solid #e5e7eb;
    border-radius: 7px;

    font-size: 14px;
    color: #444;
}

.class-item-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.class-item-info strong {
    color: #333;
}

.class-item-info span {
    font-size: 12px;
    color: #888;
}

.no-data {
    margin: 0;

    color: #999;

    font-size: 13px;
}


/* =========================
   クラス設定モーダル
========================= */

.class-modal {
    max-height: 80vh;
    overflow-y: auto;
}

.class-loading {
    padding: 30px 0;

    text-align: center;

    color: #888;

    font-size: 14px;
}

.no-class-message {
    padding: 30px 0;

    text-align: center;

    color: #999;

    font-size: 14px;
}

.select-class-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.select-class-item {
    width: 100%;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 12px 14px;

    border: 1px solid #e5e7eb;
    border-radius: 8px;

    background: #f8fafc;

    color: #333;

    text-align: left;
}

.select-class-item:hover {
    background: #e0edff;
    border-color: #bfdbfe;
}

.select-class-item:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.select-class-item>div {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.select-class-item strong {
    font-size: 14px;
}

.select-class-item span {
    font-size: 12px;
    color: #888;
}

.select-class-action {
    color: #2563eb !important;
    font-weight: 600;
}


/* =========================
   担当クラス解除
========================= */

.remove-class-button {
    padding: 5px 9px;

    border: none;
    border-radius: 6px;

    background: #fee2e2;
    color: #dc2626;

    font-size: 12px;
}

.remove-class-button:hover {
    background: #fecaca;
}

.remove-class-button:disabled {
    background: #f3f4f6;
    color: #999;
    cursor: not-allowed;
}


/* =========================
   削除
========================= */

.delete-section {
    margin-top: 28px;
    padding-top: 20px;

    border-top: 1px solid #eee;
}

.delete-button {
    width: 100%;

    padding: 11px;

    border: none;
    border-radius: 8px;

    background: #fee2e2;
    color: #dc2626;

    font-size: 14px;
    font-weight: 600;
}

.delete-button:hover {
    background: #fecaca;
}

.delete-button:disabled {
    background: #f3f4f6;
    color: #999;
    cursor: not-allowed;
}
</style>