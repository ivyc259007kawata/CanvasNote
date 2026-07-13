import { FabricImage } from 'fabric'


export function useImage(
    canvasManager,
    imageInput,
    saveHistory,
    updateActiveObject
) {


    // =========================
    // Fabric Canvas取得
    // =========================

    const fabricCanvas = () => {

        return canvasManager.canvas.value

    }

    // =========================
    // ファイル選択ダイアログを開く
    // =========================

    const openImage = () => {

        imageInput.value?.click()

    }

    // =========================
    // 画像読み込み
    // =========================
    const loadImageFromFile = (event) => {

        const canvas = fabricCanvas()
        if (!canvas) return

        const file = event.target.files[0]
        event.target.value = ''
        if (!file) return

        const reader = new FileReader()

        reader.onload = async () => {

            const img =
                await FabricImage.fromURL(
                    reader.result
                )

            img.set({

                left: 100,

                top: 100

            })

            // 最大サイズ調整

            const maxWidth = 400

            if (
                img.width &&
                img.width > maxWidth
            ) {

                img.scale(
                    maxWidth / img.width
                )

            }
            canvas.add(img)
            canvas.setActiveObject(
                img
            )
            updateActiveObject?.()
            canvas.requestRenderAll()
            saveHistory()
        }
        reader.readAsDataURL(file)
    }
    return {
        openImage,
        loadImageFromFile
    }
}