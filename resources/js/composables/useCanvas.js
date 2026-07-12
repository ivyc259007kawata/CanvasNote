import { ref } from 'vue'
import { Canvas, PencilBrush, Rect } from 'fabric'

export function useCanvas(canvasEl) {

    // Fabric Canvas
    const canvas = ref(null)

    // 初期化
    const initCanvas = () => {

        canvas.value = new Canvas(canvasEl.value, {
            width: 1000,
            height: 600,
            backgroundColor: '#ffffff',
            selection: true
        })

        // ペン
        const brush = new PencilBrush(canvas.value)
        brush.color = '#000000'
        brush.width = 3

        canvas.value.freeDrawingBrush = brush

        return canvas.value
    }

    // 破棄
    const destroyCanvas = () => {

        if (!canvas.value) return

        canvas.value.dispose()
        canvas.value = null
    }

    // テスト用
    const addDefaultRect = () => {

        if (!canvas.value) return


        const rect = new Rect({

            left: 100,
            top: 100,
            width: 120,
            height: 120,
            fill: '#ff4444'

        })


        canvas.value.add(rect)

        canvas.value.setActiveObject(rect)

        canvas.value.requestRenderAll()

    }

    return {
        canvas,
        initCanvas,
        destroyCanvas,
        addDefaultRect
    }

}