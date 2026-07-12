import { FabricImage } from 'fabric'


export function useImage(
    canvasManager,
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
    // 画像読み込み
    // =========================
    const loadImageFromFile = (file) => {
        const canvas = fabricCanvas()
        if (!canvas) return

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
        loadImageFromFile
    }
}