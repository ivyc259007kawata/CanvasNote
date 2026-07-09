import { ref } from 'vue'
import { Canvas, Rect, PencilBrush } from 'fabric'

export function useCanvas(canvasEl) {

    const canvas = ref(null)

    const initCanvas = () => {

        canvas.value = new Canvas(canvasEl.value, {
            width: 1000,
            height: 600,
            backgroundColor: '#ffffff',
            selection: true
        })

        canvas.value.freeDrawingBrush = new PencilBrush(canvas.value)
        canvas.value.freeDrawingBrush.color = '#000000'
        canvas.value.freeDrawingBrush.width = 3

        return canvas.value
    }

    const destroyCanvas = () => {
        canvas.value?.dispose()
        canvas.value = null
    }

    const addDefaultRect = () => {

        if (!canvas.value) return

        canvas.value.add(
            new Rect({
                left: 100,
                top: 100,
                width: 120,
                height: 120,
                fill: 'red'
            })
        )
    }

    return Object.assign(canvas, {
        initCanvas,
        destroyCanvas,
        addDefaultRect
    })
}