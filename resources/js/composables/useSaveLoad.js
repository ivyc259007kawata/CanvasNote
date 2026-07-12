import { ref } from 'vue'
export function useSaveLoad(canvasManager) {
    const showSaveDialog = ref(false)
    const FILE_NAME = 'CanvasNote'

    // =========================
    // Fabric Canvas取得
    // =========================

    const fabricCanvas = () => {
        return canvasManager.canvas.value
    }

    // =========================
    // 保存ダイアログ
    // =========================
    const openSaveDialog = () => {
        showSaveDialog.value = true
    }

    const closeSaveDialog = () => {
        showSaveDialog.value = false
    }

    // =========================
    // ダウンロード処理
    // =========================

    const downloadBlob = (blob, filename) => {

        const url =
            URL.createObjectURL(blob)

        const a =
            document.createElement('a')
        a.href = url
        a.download = filename
        a.click()
        URL.revokeObjectURL(url)
    }
    // =========================
    // .canvas保存
    // =========================

    const saveAsCanvas = () => {

        const canvas = fabricCanvas()
        if (!canvas) return

        const json =
            JSON.stringify(
                canvas.toJSON()
            )

        const blob =
            new Blob(
                [json],
                {
                    type: 'application/json'
                }
            )
        downloadBlob(
            blob,
            `${FILE_NAME}.canvas`
        )
        closeSaveDialog()
    }

    // =========================
    // PNG/JPG保存
    // =========================

    const saveAsImage = (format) => {

        const canvas = fabricCanvas()

        if (!canvas) return
        const dataUrl =
            canvas.toDataURL({
                format,
                quality: 1
            })

        fetch(dataUrl)
            .then(res => res.blob())
            .then(blob => {

                const ext =
                    format === 'jpeg'
                        ? 'jpg'
                        : 'png'

                downloadBlob(
                    blob,
                    `${FILE_NAME}.${ext}`
                )
            })

        closeSaveDialog()
    }

    // =========================
    // 読み込み
    // =========================

    const loadCanvasFile = async (event) => {

        const canvas = fabricCanvas()
        if (!canvas) return

        const file =
            event.target.files[0]
        event.target.value = ''
        if (!file) return

        const text =
            await file.text()

        const json =
            JSON.parse(text)
        await canvas.loadFromJSON(json)
        canvas.requestRenderAll()
    }

    return {
        showSaveDialog,
        openSaveDialog,
        closeSaveDialog,
        saveAsCanvas,
        saveAsImage,
        loadCanvasFile
    }


}