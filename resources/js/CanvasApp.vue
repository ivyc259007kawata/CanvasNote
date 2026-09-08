<template>

    <div class="app">

        <!-- =========================
             ヘッダー
        ========================== -->

        <header class="header">

            <div class="logo">
                📚 CanvasNote
            </div>

            <div class="user-area">

                <span class="user-name">
                    {{ user?.name }}
                </span>

                <form method="POST" action="/logout">

                    <input
                        type="hidden"
                        name="_token"
                        :value="csrfToken"
                    >

                    <button
                        type="submit"
                        class="logout-button"
                    >
                        ログアウト
                    </button>

                </form>

            </div>

        </header>


        <!-- =========================
             メインエリア
        ========================== -->

        <div class="main-layout">

            <!-- =========================
                 サイドバー
            ========================== -->

            <aside class="sidebar">

                <nav class="menu">

                    <!-- ホーム -->
                    <button
                        class="menu-item"
                        :class="{ active: page === 'dashboard' }"
                        @click="openDashboard"
                    >
                        <span class="menu-icon">🏠</span>
                        <span>ホーム</span>
                    </button>


                    <!-- 教材 -->
                    <button
                        class="menu-item"
                        :class="{ active: page === 'dashboard' }"
                        @click="openDashboard"
                    >
                        <span class="menu-icon">📚</span>
                        <span>教材</span>
                    </button>


                    <!-- クラス -->
                    <button
                        class="menu-item"
                        :class="{ active: page === 'class' }"
                        @click="openClassManagement"
                    >
                        <span class="menu-icon">🏫</span>
                        <span>クラス</span>
                    </button>


                    <!-- 宿題 -->
                    <button
                        class="menu-item"
                        :class="{ active: page === 'homework' }"
                        @click="openHomework"
                    >
                        <span class="menu-icon">📝</span>
                        <span>宿題</span>
                    </button>


                    <!-- クイズ -->
                    <button
                        class="menu-item"
                        :class="{ active: page === 'quiz' }"
                        @click="openQuiz"
                    >
                        <span class="menu-icon">❓</span>
                        <span>クイズ</span>
                    </button>

                </nav>


                <!-- 下部メニュー -->

                <div class="sidebar-bottom">

                    <button
                        class="menu-item"
                        @click="openSettings"
                    >
                        <span class="menu-icon">⚙️</span>
                        <span>設定</span>
                    </button>

                </div>

            </aside>


            <!-- =========================
                 コンテンツ
            ========================== -->

            <main class="content">

                <!-- 教材一覧 / ホーム -->

                <DashboardView
                    v-if="page === 'dashboard'"
                    @edit="openEditor"
                />


                <!-- 教材編集 -->

                <CanvasEditorView
                    v-else-if="page === 'editor'"
                    :lesson="lesson"
                    @back="backDashboard"
                />


                <!-- クラス管理 -->

                <ClassManagementView
                    v-else-if="page === 'class'"
                    @back="backDashboard"
                />


                <!-- 宿題 -->

                <div
                    v-else-if="page === 'homework'"
                    class="coming-soon"
                >

                    <div class="coming-icon">
                        📝
                    </div>

                    <h1>宿題</h1>

                    <p>
                        宿題機能を準備中です。
                    </p>

                </div>


                <!-- クイズ -->

                <div
                    v-else-if="page === 'quiz'"
                    class="coming-soon"
                >

                    <div class="coming-icon">
                        ❓
                    </div>

                    <h1>クイズ</h1>

                    <p>
                        クイズ機能を準備中です。
                    </p>

                </div>


                <!-- 設定 -->

                <div
                    v-else-if="page === 'settings'"
                    class="coming-soon"
                >

                    <div class="coming-icon">
                        ⚙️
                    </div>

                    <h1>設定</h1>

                    <p>
                        設定機能を準備中です。
                    </p>

                </div>

            </main>

        </div>

    </div>

</template>


<script setup>

import { ref } from 'vue'

import DashboardView
    from './views/DashboardView.vue'

import CanvasEditorView
    from './views/CanvasEditorView.vue'

import ClassManagementView
    from './views/ClassManagementView.vue'


// =========================
// ページ
// =========================

const page = ref('dashboard')

const lesson = ref(null)


// =========================
// ログインユーザー
// =========================

const user = ref(
    window.Laravel?.user ?? null
)


// =========================
// CSRF
// =========================

const csrfToken =
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content')


// =========================
// 教材編集
// =========================

function openEditor(target) {

    lesson.value = target

    page.value = 'editor'

}


// =========================
// ホーム
// =========================

function openDashboard() {

    page.value = 'dashboard'

}


// =========================
// クラス管理
// =========================

function openClassManagement() {

    page.value = 'class'

}


// =========================
// 宿題
// =========================

function openHomework() {

    page.value = 'homework'

}


// =========================
// クイズ
// =========================

function openQuiz() {

    page.value = 'quiz'

}


// =========================
// 設定
// =========================

function openSettings() {

    page.value = 'settings'

}


// =========================
// 教材一覧へ戻る
// =========================

function backDashboard() {

    page.value = 'dashboard'

}

</script>


<style scoped>

/* =========================
   アプリ全体
========================= */

.app {

    min-height: 100vh;

    background: #f8fafc;

}


/* =========================
   ヘッダー
========================= */

.header {

    height: 64px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 0 30px;

    background: white;

    border-bottom: 1px solid #ddd;

    box-sizing: border-box;

}


/* ロゴ */

.logo {

    font-size: 20px;

    font-weight: bold;

}


/* ユーザーエリア */

.user-area {

    display: flex;

    align-items: center;

    gap: 15px;

}


/* ユーザー名 */

.user-name {

    font-weight: 600;

    color: #444;

}


/* ログアウト */

.logout-button {

    padding: 8px 14px;

    border: none;

    border-radius: 8px;

    background: #ef4444;

    color: white;

    cursor: pointer;

}

.logout-button:hover {

    background: #dc2626;

}


/* =========================
   メインレイアウト
========================= */

.main-layout {

    display: flex;

    min-height: calc(100vh - 64px);

}


/* =========================
   サイドバー
========================= */

.sidebar {

    width: 220px;

    flex-shrink: 0;

    display: flex;

    flex-direction: column;

    justify-content: space-between;

    padding: 20px 12px;

    box-sizing: border-box;

    background: white;

    border-right: 1px solid #ddd;

}


/* メニュー */

.menu {

    display: flex;

    flex-direction: column;

    gap: 6px;

}


/* メニュー項目 */

.menu-item {

    width: 100%;

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 12px 14px;

    border: none;

    border-radius: 8px;

    background: transparent;

    color: #444;

    font-size: 15px;

    text-align: left;

    cursor: pointer;

}


/* マウスを乗せたとき */

.menu-item:hover {

    background: #f1f5f9;

}


/* 現在選択中 */

.menu-item.active {

    background: #e0edff;

    color: #2563eb;

    font-weight: 600;

}


/* アイコン */

.menu-icon {

    width: 24px;

    text-align: center;

    font-size: 18px;

}


/* =========================
   メインコンテンツ
========================= */

.content {

    flex: 1;

    min-width: 0;

    min-height: calc(100vh - 64px);

}


/* =========================
   準備中画面
========================= */

.coming-soon {

    min-height: calc(100vh - 64px);

    display: flex;

    flex-direction: column;

    justify-content: center;

    align-items: center;

    color: #555;

}


.coming-icon {

    font-size: 56px;

    margin-bottom: 15px;

}


.coming-soon h1 {

    margin: 0 0 10px;

}


.coming-soon p {

    margin: 0;

    color: #888;

}

</style>
