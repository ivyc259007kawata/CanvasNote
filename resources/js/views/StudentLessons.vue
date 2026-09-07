<template>

    <StudentLessonViewer v-if="selectedLesson" :lesson="selectedLesson" @back="selectedLesson = null" />

    <section v-else class="lessons">

        <h2>📚 公開されている教材</h2>

        <p v-if="loading">
            教材を読み込んでいます...
        </p>

        <p v-else-if="lessons.length === 0" class="empty">
            現在、公開されている教材はありません。
        </p>

        <div v-else class="lesson-list">

            <div v-for="lesson in lessons" :key="lesson.id" class="lesson-card">

                <h3>
                    {{ lesson.title }}
                </h3>

                <p v-if="lesson.description">
                    {{ lesson.description }}
                </p>

                <p class="teacher">
                    作成者：{{ lesson.teacher?.name }}
                </p>

                <button class="open-button" @click="openLesson(lesson)">
                    📖 教材を見る
                </button>

            </div>

        </div>

    </section>

</template>

<script setup>

import { ref, onMounted } from 'vue'
import StudentLessonViewer from './StudentLessonViewer.vue'

const lessons = ref([])
const loading = ref(true)
const selectedLesson = ref(null)

const loadLessons = async () => {

    try {

        const response =
            await fetch('/student-lessons-json')

        if (!response.ok) {
            throw new Error(
                '公開教材の取得に失敗しました'
            )
        }

        lessons.value =
            await response.json()

    } catch (error) {

        console.error(
            '公開教材取得エラー:',
            error
        )

    } finally {

        loading.value = false

    }
}

const openLesson = (lesson) => {
    selectedLesson.value = lesson
}

onMounted(() => {

    loadLessons()

})

</script>

<style scoped>
.lessons {
    margin-top: 30px;
}

.lesson-list {
    display: grid;
    grid-template-columns:
        repeat(auto-fill, minmax(280px, 1fr));

    gap: 20px;

    margin-top: 20px;
}

.lesson-card {
    padding: 20px;

    border:
        1px solid #ddd;

    border-radius: 12px;

    background: white;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.08);
}

.lesson-card h3 {
    margin-bottom: 10px;
}

.teacher {
    margin-top: 15px;

    color: #666;

    font-size: 14px;
}

.open-button {
    margin-top: 15px;

    padding: 8px 14px;

    border: none;

    border-radius: 8px;

    background: #2563eb;

    color: white;

    cursor: pointer;
}

.empty {
    margin-top: 20px;

    color: #666;
}
</style>