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

        <!-- 検索 -->
        <div class="search-area">

            <input v-model="searchText" type="text" placeholder="教材名を検索..." />

        </div>



        <!-- 教材がある場合 -->
        <div v-if="filteredLessons.length" class="lesson-grid">

            <div v-for="lesson in filteredLessons" :key="lesson.id" class="lesson-card">

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

                <p class="publish-status" :class="{
                    published: lesson.isPublished,
                    unpublished: !lesson.isPublished
                }">
                    {{ lesson.isPublished ? '🌐 公開中' : '🔒 非公開' }}
                </p>



                <!-- 操作 -->
                <div class="lesson-actions">

                    <button @click="editLesson(lesson.id)">
                        ✏ 編集
                    </button>

                    <button @click="renameLessonTitle(lesson.id)">
                        📝 名前変更
                    </button>

                    <button @click="duplicateLesson(lesson.id)">
                        📑 複製
                    </button>

                    <button @click="togglePublish(lesson.id)" :class="{ 'unpublish-button': lesson.isPublished }">
                        {{ lesson.isPublished ? '🔒 非公開にする' : '🌐 公開する' }}
                    </button>

                    <button @click="removeLesson(lesson.id)" class="delete-button">
                        🗑 削除
                    </button>

                </div>

            </div>

        </div>



        <!-- 検索結果がない場合 -->
        <div v-else-if="searchText.trim()" class="empty">
            🔍 「{{ searchText }}」に一致する教材がありません
        </div>


        <!-- 教材自体がない場合 -->
        <div v-else class="empty">
            まだ教材がありません
        </div>


    </div>

</template>



<script setup>

import { ref, computed }
    from 'vue'

import { useLessons }
    from '@/composables/useLessons'


const {
    lessons,
    addLesson,
    deleteLesson,
    getLesson,
    renameLesson,
    duplicateLesson,
    togglePublish

} = useLessons()


// =========================
// 検索
// =========================

const searchText = ref('')


// 検索結果
const filteredLessons = computed(() => {

    const keyword =
        searchText.value
            .trim()
            .toLowerCase()


    // 検索文字がなければ全件表示
    if (!keyword) {

        return lessons.value

    }


    // 教材名で検索
    return lessons.value.filter(
        lesson =>
            lesson.title
                .toLowerCase()
                .includes(keyword)
    )

})


// =========================
// 教材作成
// =========================

const createLesson = () => {

    const title =
        window.prompt(
            '教材名を入力してください'
        )


    if (!title) return


    addLesson(title)

}


// =========================
// 教材削除
// =========================

const removeLesson = (id) => {

    if (
        confirm(
            '削除しますか？'
        )
    ) {

        deleteLesson(id)

    }

}


// =========================
// 教材名変更
// =========================

const renameLessonTitle = (id) => {

    const lesson = getLesson(id)

    if (!lesson) return


    const title =
        window.prompt(
            '新しい教材名を入力してください',
            lesson.title
        )


    if (title === null) return

    if (!title.trim()) return


    renameLesson(id, title)

}





// =========================
// 親へイベント
// =========================

const emit = defineEmits([
    'edit'
])


// =========================
// 教材編集
// =========================

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
    max-width: 1400px;
    margin: 0 auto;
}

/* =========================
   タイトル
========================= */

.dashboard h1 {
    margin-bottom: 30px;
}




/* =========================
   ヘッダー
========================= */

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.header h2 {
    margin: 0;
}




/* =========================
   ボタン
========================= */

button {
    padding: 8px 16px;
    border: none;
    border-radius: 10px;
    background: #3b82f6;
    color: white;
    cursor: pointer;
    transition: 0.2s;
}

button:hover {
    opacity: 0.85;
}

/* =========================
   検索
========================= */

.search-area {
    margin-bottom: 25px;
}

.search-area input {
    width: 100%;
    max-width: 400px;

    padding: 12px 16px;

    border: 1px solid #ddd;
    border-radius: 10px;

    font-size: 14px;

    box-sizing: border-box;

    transition: 0.2s;
}

.search-area input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}





/* =========================
   教材一覧
========================= */

.lesson-grid {
    display: grid;

    grid-template-columns:
        repeat(auto-fill, minmax(330px, 1fr));

    gap: 20px;
}



/* =========================
   教材カード
========================= */

.lesson-card {
    border: 1px solid #ddd;
    border-radius: 20px;

    padding: 20px;

    background: white;

    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

.lesson-card:hover {
    transform: translateY(-3px);

    box-shadow:
        0 8px 20px rgba(0, 0, 0, 0.08);
}




/* =========================
   サムネイル
========================= */

.lesson-thumbnail {
    width: 100%;
    height: 180px;

    margin-bottom: 15px;

    border: 1px solid #ddd;
    border-radius: 12px;

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
   教材情報
========================= */

.lesson-card h3 {
    margin: 8px 0 12px;

    font-size: 18px;
}

.lesson-card p {
    margin: 6px 0;

    color: #666;

    font-size: 14px;
}




/* =========================
   操作ボタン
========================= */

.lesson-actions {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 8px;

    margin-top: 18px;
}

.lesson-card button {
    width: 100%;
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
    margin-top: 50px;
    padding: 40px;
    color: #777;
    text-align: center;
    border: 1px dashed #ddd;
    border-radius: 15px;
    background: #fafafa;
}

/* =========================
   公開状態
========================= */

.publish-status {
    display: inline-block;

    width: fit-content;

    padding: 4px 10px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: 600;
}

.publish-status.published {
    background: #ecfdf5;
    color: #059669;
}

.publish-status.unpublished {
    background: #f3f4f6;
    color: #6b7280;
}


/* 公開中に押すボタン */

.unpublish-button {
    background: #6b7280;
}

.unpublish-button:hover {
    background: #4b5563;
}


</style>
