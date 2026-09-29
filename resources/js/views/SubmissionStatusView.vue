<template>
    <div class="submission-page">

        <h1>📋 提出状況</h1>

        <h2>
            {{ lesson?.title }}
        </h2>

        <div v-if="submissions.length > 0" class="submission-list">

            <div v-for="submission in submissions" :key="submission.student_id" class="submission-card">

                <div class="student-info">
                    <h3>
                        👤 {{ submission.student?.name }}
                    </h3>

                    <p>
                        提出日時：
                        {{ submission.submitted_at || '未提出' }}
                    </p>

                    <button v-if="
                        submission.status === 'submitted' ||
                        submission.status === 'returned'
                    " class="view-answer-button" @click="viewAnswer(submission)">
                        👀 回答を見る
                    </button>

                </div>

                <div class="status" :class="{
                    submitted: submission.status === 'submitted',
                    returned: submission.status === 'returned',
                    draft: submission.status === 'draft',
                    'not-submitted': submission.status === 'not_submitted'
                }">
                    {{
                        submission.status === 'submitted'
                            ? '🟢 提出済み'
                            : submission.status === 'returned'
                                ? '📩 返却済み'
                                : submission.status === 'draft'
                                    ? '📝 下書き'
                                    : '⚪ 未着手'
                    }}
                </div>

                <!-- 採点結果 -->
                <div v-if="
                    submission.status === 'submitted' ||
                    submission.status === 'returned'
                " class="grading-result">
                    <p v-if="submission.score !== null">
                        📊 {{ submission.score }} / 100
                    </p>

                    <p v-else>
                        📝 未採点
                    </p>

                    <p v-if="submission.comment" class="grading-comment">
                        💬 {{ submission.comment }}
                    </p>
                </div>

            </div>

        </div>

        <div v-else class="empty">
            まだ提出された回答はありません。
        </div>

        <button @click="$emit('back')">
            ← 教材一覧に戻る
        </button>

    </div>
</template>

<script setup>

import { ref, onMounted } from 'vue'

const props = defineProps({
    lesson: {
        type: Object,
        required: true
    }
})

// 提出状況
const submissions = ref([])

const emit = defineEmits([
    'back',
    'view-answer'
])

const viewAnswer = (submission) => {
    console.log(
        '回答を見る:',
        submission
    )

    emit(
        'view-answer',
        submission
    )
}

// 提出状況を取得
const loadSubmissions = async () => {
    try {
        const response = await fetch(
            `/lessons/${props.lesson.id}/submissions`
        )

        if (!response.ok) {
            throw new Error(
                '提出状況の取得に失敗しました。'
            )
        }

        const data = await response.json()

        console.log(
            '提出状況取得成功:',
            data
        )

        console.log(
            '提出件数:',
            data.length
        )

        console.log(
            '1件目の提出データ:',
            data[0]
        )

        console.log(
            '1件目の回答データ:',
            data[0]?.elements
        )

        submissions.value = data

    } catch (error) {
        console.error(
            '提出状況取得エラー:',
            error
        )
    }
}

// 画面が表示されたら取得
onMounted(() => {
    loadSubmissions()
})

</script>

<style scoped>
.submission-page {
    padding: 30px;
}

button {
    margin-top: 20px;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    background: #3b82f6;
    color: white;
    cursor: pointer;
}

.submission-list {
    margin-top: 25px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.submission-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
}

.student-info h3 {
    margin: 0 0 8px;
}

.student-info p {
    margin: 0;
    color: #666;
}

.status {
    padding: 8px 14px;
    border-radius: 20px;
    font-weight: 600;
}

.status.submitted {
    background: #ecfdf5;
    color: #059669;
}

.status.draft {
    background: #f3f4f6;
    color: #6b7280;
}

.status.not-submitted {
    background: #f9fafb;
    color: #9ca3af;
}

.status.returned {
    background: #f5f3ff;
    color: #7c3aed;
}

.empty {
    margin-top: 25px;
    padding: 30px;
    text-align: center;
    color: #777;
    background: #fafafa;
    border: 1px dashed #ddd;
    border-radius: 12px;
}

.view-answer-button {
    margin-top: 12px;
    padding: 8px 14px;
    border: none;
    border-radius: 8px;
    background: #3b82f6;
    color: white;
    cursor: pointer;
}

.view-answer-button:hover {
    opacity: 0.85;
}

.grading-result {
    margin-top: 10px;
    padding: 10px 14px;
    background: #f9fafb;
    border-radius: 8px;
}

.grading-result p {
    margin: 4px 0;
}

.grading-comment {
    color: #555;
    font-size: 14px;
}
</style>