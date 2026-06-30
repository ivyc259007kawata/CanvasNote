import { ref } from 'vue'

/**
 * Fabric.js用 Undo / Redo 管理Composable
 *
 * - canvas.toJSON() を履歴として保存
 * - undo / redo 対応
 * - isRestoring中は履歴保存を止める
 */
export function useHistory(canvas) {
    const history = ref([])
    const historyIndex = ref(-1)

    // Undo/Redo中フラグ
    const isRestoring = ref(false)

    // デバウンス用タイマー
    let timer = null

    // =========================
    // スナップショット保存
    // =========================
    const save = () => {
        if (!canvas) return
        if (isRestoring.value) return

        if (timer) clearTimeout(timer)

        timer = setTimeout(() => {
            const json = JSON.stringify(canvas.toJSON())

            // 同じ状態なら保存しない
            if (history.value[historyIndex.value] === json) return

            // 現在位置より後ろを削除（分岐削除）
            history.value.splice(historyIndex.value + 1)

            history.value.push(json)
            historyIndex.value = history.value.length - 1
        }, 300)
    }

    // =========================
    // Undo
    // =========================
    const undo = async () => {
        if (!canvas) return
        if (historyIndex.value <= 0) return

        historyIndex.value--

        isRestoring.value = true

        try {
            await canvas.loadFromJSON(history.value[historyIndex.value])
            canvas.requestRenderAll()
        } finally {
            isRestoring.value = false
        }
    }

    // =========================
    // Redo
    // =========================
    const redo = async () => {
        if (!canvas) return
        if (historyIndex.value >= history.value.length - 1) return

        historyIndex.value++

        isRestoring.value = true

        try {
            await canvas.loadFromJSON(history.value[historyIndex.value])
            canvas.requestRenderAll()
        } finally {
            isRestoring.value = false
        }
    }

    // =========================
    // 初期状態を登録
    // =========================
    const init = () => {
        if (!canvas) return
        const json = JSON.stringify(canvas.toJSON())

        history.value = [json]
        historyIndex.value = 0
    }

    return {
        history,
        historyIndex,
        isRestoring,
        save,
        undo,
        redo,
        init
    }
}