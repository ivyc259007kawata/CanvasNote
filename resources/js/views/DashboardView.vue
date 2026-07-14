<template>

    <div class="dashboard">


        <h1>
            📚 CanvasNote
        </h1>


        <section class="header">

            <h2>
                教材一覧
            </h2>


            <button @click="createLesson">
                ＋ 新しい教材
            </button>


        </section>



        <div v-if="lessons.length" class="lesson-grid">


            <div v-for="lesson in lessons" :key="lesson.id" class="lesson-card">


                <h3>
                    {{ lesson.title }}
                </h3>


                <p>
                    作成日：
                    {{ lesson.created }}
                </p>


                <button @click="editLesson(lesson.id)">
                    ✏ 編集
                </button>


                <button @click="removeLesson(lesson.id)">
                    🗑 削除
                </button>


            </div>


        </div>



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



const createLesson = () => {


    const title =
        window.prompt(
            '教材名を入力してください'
        )


    if (!title) return


    addLesson(title)


}



const removeLesson = (id) => {

    if (
        confirm(
            '削除しますか？'
        )
    ) {

        deleteLesson(id)

    }

}
const emit = defineEmits([
    'edit'
])

const editLesson = (id) => {


    emit(
        'edit',
        getLesson(id)
    )


}


</script>



<style scoped>
.dashboard {

    padding: 30px;

}



.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

}



button {

    padding: 8px 16px;

    border: none;

    border-radius: 8px;

    background: #3b82f6;

    color: white;

    cursor: pointer;

}



.lesson-grid {

    display: grid;

    grid-template-columns:
        repeat(auto-fill, minmax(250px, 1fr));

    gap: 20px;

    margin-top: 20px;

}



.lesson-card {

    border: 1px solid #ddd;

    border-radius: 12px;

    padding: 20px;

    background: white;

}



.lesson-card button {

    margin-right: 8px;

}



.empty {

    margin-top: 40px;

    color: #777;

}
</style>