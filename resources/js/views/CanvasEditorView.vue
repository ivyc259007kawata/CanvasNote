<template>

    <div class="editor-page">

        <!-- =========================
             ヘッダー
        ========================= -->

        <header class="editor-header">

            <!-- 戻るボタン -->
            <button class="back-button" @click="$emit('back')">
                ← 教材一覧
            </button>


            <!-- 教材情報 -->
            <div class="lesson-info">

                <span class="lesson-label">
                    📚 教材編集
                </span>

                <h2>
                    {{ lesson?.title }}
                </h2>

            </div>


            <!-- 保存状態 -->
            <div class="save-status" :class="saveStatus">

                <span v-if="saveStatus === 'saved'">
                    ✓ 保存済み
                </span>

                <span v-else-if="saveStatus === 'saving'">
                    ⟳ 保存中...
                </span>

                <span v-else>
                    ⚠ 保存エラー
                </span>

            </div>

        </header>



        <!-- =========================
             キャンバス
        ========================= -->

        <main class="editor-content">

            <CanvasNote :lesson="lesson" @save-status="updateSaveStatus" />

        </main>

    </div>

</template>



<script setup>

import { ref } from 'vue'

import CanvasNote
    from '@/canvas/CanvasNote.vue'


defineProps({
    lesson: Object
})


defineEmits([
    'back'
])


// =========================
// 保存状態
// =========================

const saveStatus = ref('saved')


const updateSaveStatus = (status) => {

    saveStatus.value = status

}

</script>


<style scoped>
/* =========================
   編集ページ
========================= */

.editor-page {

    min-height: 100vh;

    padding: 20px 30px;

    box-sizing: border-box;

    background: #f5f7fb;

}



/* =========================
   ヘッダー
========================= */

.editor-header {

    display: flex;

    align-items: center;

    gap: 25px;

    margin-bottom: 20px;

}



/* =========================
   戻るボタン
========================= */

.back-button {

    padding: 10px 16px;

    border: 1px solid #d1d5db;

    border-radius: 10px;

    background: white;

    color: #374151;

    font-size: 14px;

    cursor: pointer;

    transition: 0.2s;

}


.back-button:hover {

    background: #f3f4f6;

    transform: translateX(-2px);

}



/* =========================
   教材情報
========================= */

.lesson-info {

    display: flex;

    flex-direction: column;

    gap: 3px;

}


.lesson-label {

    font-size: 12px;

    color: #6b7280;

}


.lesson-info h2 {

    margin: 0;

    font-size: 24px;

    color: #1f2937;

}



/* =========================
   編集エリア
========================= */

.editor-content {

    background: white;

    border-radius: 16px;

    padding: 15px;

    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.05);

}


/* =========================
   保存状態
========================= */

.save-status {

    margin-left: auto;

    padding: 8px 14px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: 500;

}


/* 保存済み */

.save-status.saved {

    background: #ecfdf5;

    color: #059669;

}


/* 保存中 */

.save-status.saving {

    background: #eff6ff;

    color: #2563eb;

}


/* 保存エラー */

.save-status.error {

    background: #fef2f2;

    color: #dc2626;

}


</style>