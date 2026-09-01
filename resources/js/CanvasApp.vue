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
                    <input type="hidden" name="_token" :value="csrfToken">

                    <button type="submit" class="logout-button">
                        ログアウト
                    </button>

                </form>

            </div>

        </header>


        <!-- =========================
             メイン
        ========================== -->

        <main>

            <DashboardView v-if="page === 'dashboard'" @edit="openEditor" />

            <CanvasEditorView v-else :lesson="lesson" @back="backDashboard" />

        </main>

    </div>

</template>


<script setup>

import { ref } from 'vue'

import DashboardView
    from './views/DashboardView.vue'

import CanvasEditorView
    from './views/CanvasEditorView.vue'


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
// 教材一覧へ戻る
// =========================

function backDashboard() {

    page.value = 'dashboard'

}

</script>


<style scoped>
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
   メイン
========================= */

main {

    min-height: calc(100vh - 64px);

}
</style>
