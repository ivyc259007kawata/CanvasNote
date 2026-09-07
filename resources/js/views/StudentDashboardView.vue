<template>
    <div class="student-dashboard">

        <header class="dashboard-header">
            <div>
                <h1>📚 生徒ホーム</h1>
                <p>ようこそ、{{ userName }} さん</p>
            </div>
        </header>

        <main class="dashboard-content">

            <h2>📖 公開教材</h2>

            <p v-if="loading" class="message">
                教材を読み込んでいます...
            </p>

            <p v-else-if="lessons.length === 0" class="message">
                現在、公開されている教材はありません。
            </p>

            <div v-else class="lesson-list">

                <div v-for="lesson in lessons" :key="lesson.id" class="lesson-card">

                    <div class="lesson-info">

                        <h3>
                            {{ lesson.title }}
                        </h3>

                        <p v-if="lesson.description" class="description">
                            {{ lesson.description }}
                        </p>

                        <p class="teacher">
                            作成者：
                            {{ lesson.teacher?.name ?? '先生' }}
                        </p>

                    </div>

                    <button class="open-button" @click="openLesson(lesson)">
                        📖 教材を見る
                    </button>

                </div>

            </div>

        </main>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const userName = ref(
    window.Laravel?.user?.name ?? '生徒'
)

const lessons = ref([])
const loading = ref(true)

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
    console.log(
        '教材を開く:',
        lesson
    )

    // 教材を開く処理は後で実装
}

onMounted(() => {
    loadLessons()
})
</script>

<style scoped>
.student-dashboard {
    min-height: 100vh;
    background: #f5f7fb;
}

.dashboard-header {
    padding: 30px 40px;
    background: white;
    border-bottom: 1px solid #ddd;
}

.dashboard-header h1 {
    margin: 0;
    font-size: 28px;
}

.dashboard-header p {
    margin-top: 8px;
    color: #666;
}

.dashboard-content {
    padding: 40px;
}

.dashboard-content h2 {
    margin-bottom: 20px;
}

.message {
    padding: 30px;
    background: white;
    border-radius: 12px;
    color: #666;
}

.lesson-list {
    display: grid;
    grid-template-columns:
        repeat(auto-fill, minmax(280px, 1fr));

    gap: 20px;
}

.lesson-card {
    padding: 24px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.06);
}

.lesson-card h3 {
    margin: 0 0 12px;
    font-size: 20px;
}

.description {
    color: #555;
}

.teacher {
    margin-top: 16px;
    color: #777;
    font-size: 14px;
}

.open-button {
    margin-top: 20px;
    padding: 9px 16px;

    border: none;
    border-radius: 8px;

    background: #2563eb;
    color: white;

    cursor: pointer;
}

.open-button:hover {
    opacity: 0.9;
}
</style>
