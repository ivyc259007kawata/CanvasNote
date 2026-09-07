import { ref } from 'vue'

export function useLessons() {

    const lessons = ref([])

    // MySQLから教材を取得
    const loadLessons = async () => {
        try {
            const response = await fetch('/lessons-json')

            if (!response.ok) {
                throw new Error('教材の取得に失敗しました')
            }

            const data = await response.json()

            lessons.value = data.map(lesson => ({
                id: lesson.id,
                title: lesson.title,
                description: lesson.description,
                created: lesson.created_at,
                isPublished: Boolean(lesson.is_public),

                // Canvas側との互換性を保つ
                pages: [
                    {
                        id: 1,
                        title: 'ページ1',
                        canvasData: null
                    }
                ]
            }))

        } catch (error) {
            console.error('教材取得エラー:', error)
        }
    }

    // 初回読み込み
    loadLessons()

    const addLesson = async (title) => {
        try {
            const response = await fetch('/lessons-json', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content')
                },
                body: JSON.stringify({
                    title: title
                })
            })

            if (!response.ok) {
                throw new Error('教材の作成に失敗しました')
            }

            const data = await response.json()

            const lesson = {
                id: data.id,
                title: data.title,
                description: data.description,
                created: data.created_at,
                isPublished: Boolean(data.is_public),

                pages: [
                    {
                        id: 1,
                        title: 'ページ1',
                        canvasData: null
                    }
                ]
            }

            lessons.value.push(lesson)

            return lesson

        } catch (error) {
            console.error('教材作成エラー:', error)
        }
    }

    const renameLesson = async (id, newTitle) => {
        try {
            newTitle = newTitle.trim()

            if (!newTitle) return

            const response = await fetch(
                `/lessons-json/${id}`,
                {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        title: newTitle
                    })
                }
            )

            if (!response.ok) {
                throw new Error(
                    '教材名の変更に失敗しました'
                )
            }

            const data = await response.json()

            const lesson = lessons.value.find(
                lesson => lesson.id === id
            )

            if (lesson) {
                lesson.title = data.title
            }

        } catch (error) {
            console.error(
                '教材名変更エラー:',
                error
            )
        }
    }

    const duplicateLesson = async (id) => {
        try {
            const response = await fetch(
                `/lessons-json/${id}/duplicate`,
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.getAttribute('content')
                    }
                }
            )

            if (!response.ok) {
                throw new Error(
                    '教材の複製に失敗しました'
                )
            }

            const data = await response.json()

            const lesson = {
                id: data.id,
                title: data.title,
                description: data.description,
                created: data.created_at,
                isPublished: Boolean(data.is_public),

                pages: [
                    {
                        id: 1,
                        title: 'ページ1',
                        canvasData: null
                    }
                ]
            }

            lessons.value.push(lesson)

            return lesson

        } catch (error) {
            console.error(
                '教材複製エラー:',
                error
            )
        }
    }

    const deleteLesson = async (id) => {
        try {
            const response = await fetch(
                `/lessons-json/${id}`,
                {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.getAttribute('content')
                    }
                }
            )

            if (!response.ok) {
                throw new Error(
                    '教材の削除に失敗しました'
                )
            }

            lessons.value =
                lessons.value.filter(
                    lesson => lesson.id !== id
                )

        } catch (error) {
            console.error(
                '教材削除エラー:',
                error
            )
        }
    }

    const getLesson = (id) => {
        return lessons.value.find(
            lesson => lesson.id === id
        )
    }

    const togglePublish = async (id) => {
        try {
            const response = await fetch(
                `/lessons-json/${id}/publish`,
                {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.getAttribute('content')
                    }
                }
            )

            if (!response.ok) {
                throw new Error(
                    '公開状態の変更に失敗しました'
                )
            }

            const data = await response.json()

            const lesson = lessons.value.find(
                lesson => lesson.id === id
            )

            if (lesson) {
                lesson.isPublished =
                    Boolean(data.is_public)
            }

        } catch (error) {
            console.error(
                '公開状態変更エラー:',
                error
            )
        }
    }

    return {
        lessons,
        addLesson,
        deleteLesson,
        getLesson,
        renameLesson,
        duplicateLesson,
        togglePublish
    }
}