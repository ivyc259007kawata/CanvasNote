import { ref } from 'vue'
import { Canvas, PencilBrush, Rect, Object as FabricObject } from 'fabric'

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

        // ★ ハンドルの見た目をExcel/Figma風に
        FabricObject.prototype.set({
            cornerStyle: 'circle',       // 四隅を丸いハンドルに
            cornerColor: '#3b82f6',
            cornerStrokeColor: '#ffffff',
            cornerSize: 10,
            transparentCorners: false,
            borderColor: '#3b82f6',
            borderScaleFactor: 2,
            padding: 4
        })


        // ペン
        const brush = new PencilBrush(canvas.value)
        brush.color = '#000000'
        brush.width = 3

        canvas.value.freeDrawingBrush = brush

        return canvas.value
    }

    const setBrushColor = (color) => {

        if (!canvas.value) return

        canvas.value.freeDrawingBrush.color = color

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
        addDefaultRect,
        setBrushColor
    }

}