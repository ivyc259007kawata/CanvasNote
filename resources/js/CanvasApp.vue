

<template>
  <div>
    <h2>CanvasNote</h2>
    <canvas ref="canvasEl" width="1000" height="600"></canvas>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { Canvas, Rect } from 'fabric'

const canvasEl = ref(null)

console.log('canvas init')

onMounted(() => {
    const canvas = new Canvas(canvasEl.value, {
        width: 1000,
        height: 600,
        backgroundColor: '#ffffff'
    })

    const rect = new Rect({
        left: 100,
        top: 100,
        width: 120,
        height: 120,
        fill: 'black'
    })

    canvas.add(rect)

    canvas.on('mouse:down', (opt) => {
        const p = canvas.getPointer(opt.e)

        canvas.add(new Rect({
            left: p.x,
            top: p.y,
            width: 100,
            height: 100,
            fill: 'red'
        }))

        canvas.requestRenderAll()
    })
})
</script>