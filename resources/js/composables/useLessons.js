import { ref } from 'vue'


export function useLessons() {


    const lessons = ref(
        JSON.parse(
            localStorage.getItem('lessons') || '[]'
        )
    )


    // 保存
    const save = () => {

        localStorage.setItem(
            'lessons',
            JSON.stringify(lessons.value)
        )

    }



    // 教材追加
    const addLesson = (title) => {

        const lesson = {

            id: Date.now(),

            title,

            created:
                new Date()
                    .toLocaleDateString(),

            canvasData: null

        }


        lessons.value.push(lesson)

        save()

        return lesson

    }



    // 削除
    const deleteLesson = (id) => {


        lessons.value =
            lessons.value.filter(
                lesson =>
                    lesson.id !== id
            )


        save()

    }



    // 取得
    const getLesson = (id) => {

        return lessons.value.find(
            lesson =>
                lesson.id === id
        )

    }



    return {

        lessons,

        addLesson,

        deleteLesson,

        getLesson

    }

}