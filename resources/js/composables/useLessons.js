import { ref } from 'vue'


export function useLessons() {


    const lessons = ref(
        JSON.parse(
            localStorage.getItem('lessons') || '[]'
        ).map(lesson => {

            // =========================
            // 古いデータを新形式へ変換
            // =========================

            if (!lesson.pages) {

                lesson.pages = [
                    {
                        id: 1,
                        title: 'ページ1',
                        canvasData: lesson.canvasData ?? null
                    }
                ]

                delete lesson.canvasData

            }


            // =========================
            // 公開状態
            // =========================

            // isPublished が存在しない
            // 古い教材は非公開にする

            if (lesson.isPublished === undefined) {

                lesson.isPublished = false

            }


            return lesson

        })
    )


    // =========================
    // 保存
    // =========================

    const save = () => {

        localStorage.setItem(
            'lessons',
            JSON.stringify(lessons.value)
        )

    }


    // =========================
    // 教材追加
    // =========================

    const addLesson = (title) => {

        const lesson = {

            id: Date.now(),

            title,

            created:
                new Date()
                    .toLocaleDateString(),

            // 新しく作った教材は非公開
            isPublished: false,

            pages: [
                {
                    id: 1,
                    title: 'ページ1',
                    canvasData: null
                }
            ]

        }


        lessons.value.push(lesson)

        save()

        return lesson

    }


    // =========================
    // 教材名変更
    // =========================

    const renameLesson = (id, newTitle) => {

        const lesson = lessons.value.find(
            lesson => lesson.id === id
        )

        if (!lesson) return

        newTitle = newTitle.trim()

        if (!newTitle) return

        lesson.title = newTitle

        save()

    }


    // =========================
    // 教材複製
    // =========================

    const duplicateLesson = (id) => {

        const original = lessons.value.find(
            lesson => lesson.id === id
        )

        if (!original) return


        const copy = JSON.parse(
            JSON.stringify(original)
        )


        copy.id = Date.now()

        copy.title =
            `${original.title}（コピー）`

        copy.created =
            new Date().toLocaleDateString()


        copy.pages =
            copy.pages.map(page => ({
                ...page,
                id: Date.now() + Math.random()
            }))


        lessons.value.push(copy)

        save()

        return copy

    }


    // =========================
    // 削除
    // =========================

    const deleteLesson = (id) => {

        lessons.value =
            lessons.value.filter(
                lesson =>
                    lesson.id !== id
            )

        save()

    }


    // =========================
    // 取得
    // =========================

    const getLesson = (id) => {

        return lessons.value.find(
            lesson =>
                lesson.id === id
        )

    }

    // 教材の公開・非公開
    const togglePublish = (id) => {

        const lesson = lessons.value.find(
            lesson => lesson.id === id
        )

        if (!lesson) return

        lesson.isPublished =
            !lesson.isPublished

        save()
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