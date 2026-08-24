<template>

    <div class="dashboard">


        <!-- タイトル -->
        <h1>
            📚 CanvasNote
        </h1>


        <!-- ヘッダー -->
        <section class="header">

            <h2>
                教材一覧
            </h2>


            <button @click="createLesson">
                ＋ 新しい教材
            </button>

        </section>



        <!-- 教材がある場合 -->
        <div v-if="lessons.length" class="lesson-grid">

            <div v-for="lesson in lessons" :key="lesson.id" class="lesson-card">

                <!-- サムネイル -->
                <div class="lesson-thumbnail">

                    <img v-if="lesson.pages?.[0]?.thumbnail" :src="lesson.pages[0].thumbnail" alt="教材サムネイル" />

                    <div v-else class="empty-thumbnail">
                        📄
                    </div>

                </div>



                <!-- 教材情報 -->
                <h3>
                    {{ lesson.title }}
                </h3>


                <p>
                    作成日：
                    {{ lesson.created }}
                </p>


                <p>
                    📄
                    {{ lesson.pages?.length || 0 }}ページ
                </p>



                <!-- 操作 -->
                <div class="lesson-actions">

                    <button @click="editLesson(lesson.id)">
                        ✏ 編集
                    </button>


                    <button @click="removeLesson(lesson.id)" class="delete-button">
                        🗑 削除
                    </button>

                </div>

            </div>

        </div>



        <!-- 教材がない場合 -->
        <div v-else class="empty">

            まだ教材がありません

        </div>


    </div>

</template>



<script setup>


import { useLessons }
    from '@/composables/useLessons'



const {
    lessons,
    addLesson,
    deleteLesson,
    getLesson

} = useLessons()



// 教材作成
const createLesson = () => {

    const title =
        window.prompt(
            '教材名を入力してください'
        )


    if (!title) return


    addLesson(title)

}



// 教材削除
const removeLesson = (id) => {

    if (
        confirm(
            '削除しますか？'
        )
    ) {

        deleteLesson(id)

    }

}



// 親へイベントを送る
const emit = defineEmits([
    'edit'
])



// 教材編集
const editLesson = (id) => {

    emit(
        'edit',
        getLesson(id)
    )

}


</script>



<style scoped>
/* =========================
   ダッシュボード
========================= */

.dashboard {

    padding: 30px;

}



/* =========================
   ヘッダー
========================= */

.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

}



/* =========================
   ボタン
========================= */

button {

    padding: 8px 16px;

    border: none;

    border-radius: 8px;

    background: #3b82f6;

    color: white;

    cursor: pointer;

}



/* =========================
   教材一覧
========================= */

.lesson-grid {

    display: grid;

    grid-template-columns:
        repeat(auto-fill,
            minmax(250px, 1fr));

    gap: 20px;

    margin-top: 20px;

}



/* =========================
   教材カード
========================= */

.lesson-card {

    border: 1px solid #ddd;

    border-radius: 12px;

    padding: 20px;

    background: white;

}



/* =========================
   サムネイル
========================= */

.lesson-thumbnail {

    width: 100%;

    height: 150px;

    margin-bottom: 15px;

    border: 1px solid #ddd;

    border-radius: 8px;

    overflow: hidden;

    background: #f8f8f8;

}



.lesson-thumbnail img {

    width: 100%;

    height: 100%;

    object-fit: contain;

}



/* サムネイルがない場合 */

.empty-thumbnail {

    width: 100%;

    height: 100%;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 40px;

    color: #999;

}



/* =========================
   操作ボタン
========================= */

.lesson-actions {

    display: flex;

    gap: 8px;

    margin-top: 15px;

}



.lesson-card button {

    margin-right: 8px;

}



/* 削除ボタン */

.delete-button {

    background: #ef4444;

}



.delete-button:hover {

    background: #dc2626;

}



/* =========================
    教材がない場合
========================= */

.empty {

    margin-top: 40px;

    color: #777;

}
</style>