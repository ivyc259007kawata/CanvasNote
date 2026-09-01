<template>

    <div class="homework-page">

        <!-- =========================
             タイトル
        ========================= -->

        <div class="page-header">

            <div>
                <h1>📝 宿題管理</h1>

                <p>
                    作成した教材を宿題として管理できます。
                </p>
            </div>

        </div>


        <!-- =========================
             宿題一覧
        ========================= -->

        <div v-if="homeworks.length" class="homework-grid">

            <div
                v-for="homework in homeworks"
                :key="homework.id"
                class="homework-card"
            >

                <!-- 教材情報 -->

                <div class="homework-info">

                    <span class="homework-label">
                        📝 宿題
                    </span>

                    <h2>
                        {{ homework.title }}
                    </h2>

                    <p>
                        作成日：{{ homework.created }}
                    </p>

                    <p>
                        📄 {{ homework.pages }}ページ
                    </p>

                </div>


                <!-- 状態 -->

                <div
                    class="status"
                    :class="{
                        published: homework.isPublished,
                        unpublished: !homework.isPublished
                    }"
                >

                    {{
                        homework.isPublished
                            ? '🌐 公開中'
                            : '🔒 非公開'
                    }}

                </div>


                <!-- 操作 -->

                <div class="homework-actions">

                    <button
                        @click="toggleHomework(homework)"
                    >

                        {{
                            homework.isPublished
                                ? '🔒 非公開にする'
                                : '🌐 公開する'
                        }}

                    </button>

                    <button
                        class="detail-button"
                        @click="showHomework(homework)"
                    >
                        📄 詳細
                    </button>

                </div>

            </div>

        </div>


        <!-- =========================
             宿題がない場合
        ========================= -->

        <div v-else class="empty">

            <div class="empty-icon">
                📝
            </div>

            <h2>
                まだ宿題がありません
            </h2>

            <p>
                教材一覧から教材を作成し、
                宿題として登録できます。
            </p>

        </div>


    </div>

</template>


<script setup>

import { ref } from 'vue'


// =========================
// 宿題データ
// =========================

const homeworks = ref([])


// =========================
// 宿題の公開・非公開
// =========================

const toggleHomework = (homework) => {

    homework.isPublished =
        !homework.isPublished

}


// =========================
// 詳細
// =========================

const showHomework = (homework) => {

    alert(
        `宿題名：${homework.title}\n` +
        `ページ数：${homework.pages}ページ`
    )

}

</script>


<style scoped>

/* =========================
   ページ
========================= */

.homework-page {

    padding: 30px;

    max-width: 1400px;

    margin: 0 auto;

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

}

.page-header p {

    margin: 0;

    color: #6b7280;

}


/* =========================
   宿題一覧
========================= */

.homework-grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fill,
            minmax(320px, 1fr)
        );

    gap: 20px;

}


/* =========================
   宿題カード
========================= */

.homework-card {

    padding: 24px;

    background: white;

    border:
        1px solid #e5e7eb;

    border-radius: 20px;

    box-shadow:
        0 4px 15px
        rgba(0, 0, 0, 0.04);

    transition:
        transform 0.2s,
        box-shadow 0.2s;

}

.homework-card:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 8px 20px
        rgba(0, 0, 0, 0.08);

}


/* =========================
   宿題情報
========================= */

.homework-label {

    display: inline-block;

    margin-bottom: 8px;

    font-size: 13px;

    color: #6b7280;

}

.homework-info h2 {

    margin: 0 0 12px;

    font-size: 20px;

    color: #1f2937;

}

.homework-info p {

    margin: 6px 0;

    color: #6b7280;

    font-size: 14px;

}


/* =========================
   公開状態
========================= */

.status {

    display: inline-block;

    margin-top: 15px;

    padding: 5px 12px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: 600;

}

.status.published {

    background: #ecfdf5;

    color: #059669;

}

.status.unpublished {

    background: #f3f4f6;

    color: #6b7280;

}


/* =========================
   ボタン
========================= */

.homework-actions {

    display: flex;

    gap: 8px;

    margin-top: 20px;

}

.homework-actions button {

    flex: 1;

    padding: 9px 12px;

    border: none;

    border-radius: 10px;

    background: #3b82f6;

    color: white;

    cursor: pointer;

    transition: 0.2s;

}

.homework-actions button:hover {

    opacity: 0.85;

}

.homework-actions .detail-button {

    background: #6b7280;

}


/* =========================
   空状態
========================= */

.empty {

    margin-top: 50px;

    padding: 60px 20px;

    text-align: center;

    border:
        1px dashed #d1d5db;

    border-radius: 20px;

    background: #fafafa;

}

.empty-icon {

    font-size: 50px;

    margin-bottom: 15px;

}

.empty h2 {

    margin: 0 0 10px;

    color: #374151;

}

.empty p {

    margin: 0;

    color: #6b7280;

}

</style>