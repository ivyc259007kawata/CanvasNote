import { ref } from 'vue'

export function useAutoSave(
    lesson,
    pages,
    saveLesson
) {

    const timer = ref(null)

    // 保存状態
    const saveStatus = ref('saved')


    // =========================
    // 自動保存開始
    // =========================

    const start = () => {

        if (timer.value) {
            return
        }

        timer.value = setInterval(() => {

            if (!lesson.value) {
                return
            }

            // 保存中
            saveStatus.value = 'saving'

            try {

                pages.saveCurrentPage()

                saveLesson(false)

                // 保存成功
                saveStatus.value = 'saved'

            } catch (error) {

                console.error(
                    '自動保存に失敗しました',
                    error
                )

                // 保存失敗
                saveStatus.value = 'error'

            }

        }, 30000)

    }


    // =========================
    // 自動保存停止
    // =========================

    const stop = () => {

        if (timer.value) {

            clearInterval(timer.value)

            timer.value = null

        }

    }


    return {

        start,
        stop,
        saveStatus

    }

}