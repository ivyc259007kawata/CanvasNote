import { ref } from 'vue'

export function useLessonPages(lesson, canvas, history) {

    const currentPage = ref(0)

    // 現在ページを保存
    const saveCurrentPage = () => {

        const fc = canvas.canvas.value

        if (!fc || !lesson.value) return

        const page = lesson.value.pages[currentPage.value]

        if (!page) return

        page.canvasData = fc.toJSON()

        page.thumbnail = fc.toDataURL({
            format: 'png',
            multiplier: 0.2
        })
    }

    // 現在ページを読み込み
    const loadCurrentPage = () => {

        const fc = canvas.canvas.value

        if (!fc || !lesson.value) return

        const page = lesson.value.pages[currentPage.value]

        if (!page) return

        fc.clear()
        fc.backgroundColor = '#ffffff'

        if (page.canvasData) {

            fc.loadFromJSON(page.canvasData, () => {

                fc.requestRenderAll()

                history.init()

            })

        } else {

            fc.requestRenderAll()

            history.init()

        }

    }

    // ページ切り替え
    const changePage = (index) => {

        saveCurrentPage()

        currentPage.value = index

        loadCurrentPage()

    }

    // ページ追加
    const addPage = () => {

        saveCurrentPage()

        lesson.value.pages.push({

            id: Date.now(),

            title: `ページ${lesson.value.pages.length + 1}`,

            canvasData: null,

            thumbnail: null

        })

        currentPage.value = lesson.value.pages.length - 1

        loadCurrentPage()

    }

    // ページ削除
    const deletePage = (index) => {

        if (lesson.value.pages.length === 1) {

            alert('最後のページは削除できません')

            return

        }

        lesson.value.pages.splice(index, 1)

        if (currentPage.value > index) {

            currentPage.value--

        } else if (currentPage.value >= lesson.value.pages.length) {

            currentPage.value = lesson.value.pages.length - 1

        }

        loadCurrentPage()

    }

    // ページ名変更
    const renamePage = ({ index, title }) => {

        title = title.trim()

        if (!title) return

        lesson.value.pages[index].title = title

    }

    // ページ並び替え
    const movePage = ({ oldIndex, newIndex }) => {

        const page = lesson.value.pages.splice(oldIndex, 1)[0]

        lesson.value.pages.splice(newIndex, 0, page)

        if (currentPage.value === oldIndex) {

            currentPage.value = newIndex

        }
        else if (
            oldIndex < currentPage.value &&
            newIndex >= currentPage.value
        ) {

            currentPage.value--

        }
        else if (
            oldIndex > currentPage.value &&
            newIndex <= currentPage.value
        ) {

            currentPage.value++

        }

    }

    return {

        currentPage,

        saveCurrentPage,

        loadCurrentPage,

        changePage,

        addPage,

        deletePage,

        renamePage,

        movePage

    }

}