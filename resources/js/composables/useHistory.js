import { ref } from 'vue'

export function useHistory(canvas) {

    const history = ref([])
    const historyIndex = ref(-1)

    const isRestoring = ref(false)

    let timer = null

    const fabricCanvas = () => canvas.value.value

    // =========================
    // 履歴保存
    // =========================
    const saveHistory = () => {

        if (!fabricCanvas()) return
        if (isRestoring.value) return

        clearTimeout(timer)

        timer = setTimeout(() => {

            const json = JSON.stringify(
                fabricCanvas().toJSON()
            )

            if (history.value[historyIndex.value] === json) return

            history.value.splice(historyIndex.value + 1)

            history.value.push(json)

            historyIndex.value = history.value.length - 1

        }, 300)

    }

    // =========================
    // Undo
    // =========================
    const undo = async () => {

        if (!fabricCanvas()) return
        if (historyIndex.value <= 0) return

        historyIndex.value--

        isRestoring.value = true

        try {

            await fabricCanvas().loadFromJSON(
                history.value[historyIndex.value]
            )

            fabricCanvas().requestRenderAll()

        } finally {

            isRestoring.value = false

        }

    }

    // =========================
    // Redo
    // =========================
    const redo = async () => {

        if (!fabricCanvas()) return
        if (historyIndex.value >= history.value.length - 1) return

        historyIndex.value++

        isRestoring.value = true

        try {

            await fabricCanvas().loadFromJSON(
                history.value[historyIndex.value]
            )

            fabricCanvas().requestRenderAll()

        } finally {

            isRestoring.value = false

        }

    }

    // =========================
    // 初期履歴
    // =========================
    const init = () => {

        if (!fabricCanvas()) return

        const json = JSON.stringify(
            fabricCanvas().toJSON()
        )

        history.value = [json]

        historyIndex.value = 0

    }

    return {

        history,
        historyIndex,

        isRestoring,

        saveHistory,

        undo,
        redo,

        init

    }

}