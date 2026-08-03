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



        // ハンドル設定
        FabricObject.prototype.set({

            cornerStyle: 'circle',

            cornerColor: '#3b82f6',

            cornerStrokeColor: '#ffffff',

            cornerSize: 10,

            transparentCorners: false,

            borderColor: '#3b82f6',

            borderScaleFactor: 2,

            padding: 4

        })

        // ペン設定
        const brush =
            new PencilBrush(canvas.value)
        brush.color = '#000000'
        brush.width = 5

        canvas.value.freeDrawingBrush = brush
        return canvas.value

    }


    // ペン色変更
    const setBrushColor = (color) => {
        if (!canvas.value) return
        canvas.value.freeDrawingBrush.color = color
    }


    // ペン太さ変更
    const setBrushWidth = (width) => {
        if (!canvas.value) return
        canvas.value.freeDrawingBrush.width = width
    }

    // ツール変更
    const setTool = (tool) => {

        if (!canvas.value) return


        const brush =
            canvas.value.freeDrawingBrush
        if (!brush) return

        canvas.value.isDrawingMode = false



        if (tool === 'pen') {

            brush.color = '#000000'

            brush.width = 5

            brush.shadow = null


            canvas.value.isDrawingMode = true


        }
        else if (tool === 'marker') {

            brush.color = 'rgba(255,255,0,0.4)'

            brush.width = 25

            brush.shadow = null


            canvas.value.isDrawingMode = true


        }


        canvas.value.requestRenderAll()

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
        setBrushColor,
        setBrushWidth,
        setTool


    }

}