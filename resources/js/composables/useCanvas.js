import { ref } from 'vue'
import { Canvas, PencilBrush } from 'fabric'

export function useCanvas(canvasEl) {
  const canvas = ref(null)

  const initCanvas = () => {
    canvas.value = new Canvas(canvasEl.value, {
      width: 1000,
      height: 600,
      backgroundColor: '#fff'
    })

    canvas.value.freeDrawingBrush = new PencilBrush(canvas.value)
    canvas.value.freeDrawingBrush.color = '#000'
    canvas.value.freeDrawingBrush.width = 3

    return canvas.value
  }

  const destroyCanvas = () => {
    canvas.value?.dispose()
    canvas.value = null
  }

  return {
    canvas,
    initCanvas,
    destroyCanvas
  }
}