import { ref } from 'vue'

export function useLessonPages(lesson, canvas, history) {

    const currentPage = ref(0)


    // ==========================================
    // MySQLからページを読み込む
    // ==========================================
    const loadPagesFromServer = async () => {

        if (!lesson.value) return

        try {

            const response = await fetch(
                `/lessons-json/${lesson.value.id}/canvas`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            )

            if (!response.ok) {
                throw new Error(
                    'Canvasデータの取得に失敗しました'
                )
            }

            const data = await response.json()

            // MySQLに保存されたページがある場合
            if (data.pages && data.pages.length > 0) {

                lesson.value.pages =
                    data.pages.map(page => ({
                        id: page.id,
                        title: `ページ${page.page_number}`,
                        canvasData: page.content,
                        thumbnail: null
                    }))

            }

            // ページが1枚も保存されていない場合
            else {

                lesson.value.pages = [
                    {
                        id: Date.now(),
                        title: 'ページ1',
                        canvasData: null,
                        thumbnail: null
                    }
                ]

            }

            currentPage.value = 0

        } catch (error) {

            console.error(
                'Canvas読み込みエラー:',
                error
            )

        }
    }


    // ==========================================
    // 現在ページ保存
    // ==========================================
    const saveCurrentPage = () => {

        const fc = canvas.canvas.value

        if (!fc || !lesson.value) return

        const page =
            lesson.value.pages[currentPage.value]

        if (!page) return

        page.canvasData =
            fc.toJSON()

        page.thumbnail =
            fc.toDataURL({
                format: 'png',
                quality: 0.5,
                multiplier: 0.2
            })
    }


    // ==========================================
    // ページ読み込み
    // ==========================================
    const loadCurrentPage = async () => {
        const fc = canvas.canvas.value

        if (!fc || !lesson.value) return

        const page =
            lesson.value.pages[currentPage.value]

        if (!page) return

        // 現在のCanvasをクリア
        fc.clear()

        // 保存されたCanvasデータがある場合
        if (page.canvasData) {
            try {
                await fc.loadFromJSON(page.canvasData)

                fc.requestRenderAll()
            } catch (error) {
                console.error(
                    'Canvas復元エラー:',
                    error
                )
            }
        }
        else {
            fc.requestRenderAll()
        }
    }


    // ==========================================
    // ページ追加
    // ==========================================
    const addPage = () => {

        saveCurrentPage()

        lesson.value.pages.push({
            id: Date.now(),
            title:
                `ページ${lesson.value.pages.length + 1}`,
            canvasData: null,
            thumbnail: null
        })

        currentPage.value =
            lesson.value.pages.length - 1

        loadCurrentPage()
    }


    // ==========================================
    // ページ削除
    // ==========================================
    const deletePage = (index) => {

        if (
            lesson.value.pages.length === 1
        ) {
            alert(
                '最後のページは削除できません'
            )
            return
        }

        lesson.value.pages.splice(
            index,
            1
        )

        if (currentPage.value > index) {
            currentPage.value--
        }
        else if (
            currentPage.value >= lesson.value.pages.length
        ) {
            currentPage.value =
                lesson.value.pages.length - 1
        }

        loadCurrentPage()
    }


    // ==========================================
    // ページ変更
    // ==========================================
    const changePage = (index) => {

        saveCurrentPage()

        currentPage.value =
            index

        loadCurrentPage()
    }


    // ==========================================
    // 名前変更
    // ==========================================
    const renamePage = ({ index, title }) => {

        title =
            title.trim()

        if (!title) return

        lesson.value.pages[index].title =
            title
    }


    // ==========================================
    // 並び替え
    // ==========================================
    const movePage = ({ oldIndex, newIndex }) => {

        const page =
            lesson.value.pages.splice(
                oldIndex,
                1
            )[0]

        lesson.value.pages.splice(
            newIndex,
            0,
            page
        )

        if (currentPage.value === oldIndex) {

            currentPage.value =
                newIndex

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

        loadPagesFromServer,

        saveCurrentPage,

        loadCurrentPage,

        changePage,

        addPage,

        deletePage,

        renamePage,

        movePage

    }
}