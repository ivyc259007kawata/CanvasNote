<template>

    <div class="canvas-page">

        <div class="toolbar">
            <button @click="currentTool = 'select'" :class="{ active: currentTool === 'select' }">
                🖱 選択
            </button>

            <button @click="currentTool = 'rectangle'" :class="{ active: currentTool === 'rectangle' }">
                ▭ 四角
            </button>

            <button @click="currentTool = 'text'" :class="{ active: currentTool === 'text' }">
                📝 テキスト
            </button>

            <button @click="currentTool = 'pen'" :class="{ active: currentTool === 'pen' }">
                ✏ ペン
            </button>
            <div class="color-picker">

                <button class="color" style="background:#000000" @click="currentColor = '#000000'">
                </button>

                <button class="color" style="background:#ff0000" @click="currentColor = '#ff0000'">
                </button>

                <button class="color" style="background:#0066ff" @click="currentColor = '#0066ff'">
                </button>

                <button class="color" style="background:#00aa00" @click="currentColor = '#00aa00'">
                </button>

            </div>

            <button @click="saveCanvas">
                💾 保存
            </button>
        </div>

        <canvas ref="canvasEl" width="1000" height="600">
        </canvas>

    </div>

</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { Canvas, Rect, IText, PencilBrush } from 'fabric'

const canvasEl = ref(null)
const currentTool = ref('select')

let canvas = null
let handleKeydown = null
const currentColor = ref('#000000')

// =========================
// 保存
// =========================
const saveCanvas = () => {
    if (!canvas) return

    const json = canvas.toJSON()
    localStorage.setItem('canvas', JSON.stringify(json))

    console.log('saved')
}

onMounted(() => {

    if (!canvasEl.value) return

    // =========================
    // Fabric 初期化
    // =========================
    canvas = new Canvas(canvasEl.value, {
        width: 1000,
        height: 600,
        backgroundColor: '#ffffff',
        selection: true
    })

    canvas.renderAll()

    canvas.freeDrawingBrush = new PencilBrush(canvas)
    canvas.freeDrawingBrush.color = '#000000'
    canvas.freeDrawingBrush.width = 3



    // =========================
    // 初期オブジェクト
    // =========================
    canvas.add(
        new Rect({
            left: 100,
            top: 100,
            width: 120,
            height: 120,
            fill: `hsl(${Math.random() * 360},70%,60%)`
        })
    )

    // =========================
    // キャンバスクリック
    // =========================
    canvas.on('mouse:down', (opt) => {

        // 選択モード
        if (currentTool.value === 'select') {
            return
        }

        // ペンモード
        if (currentTool.value === 'pen') {
            return
        }

        // 四角モード
        if (currentTool.value === 'rectangle') {

            if (opt.target) return

            const pointer = canvas.getScenePoint(opt.e)

            const rect = new Rect({
                left: pointer.x,
                top: pointer.y,
                width: 100,
                height: 100,
                fill: currentColor.value
            })

            canvas.add(rect)
            canvas.setActiveObject(rect)
            canvas.renderAll()

            return
        }

        // テキストモード
        if (currentTool.value === 'text') {

            if (opt.target) return

            const pointer = canvas.getScenePoint(opt.e)

            const text = new IText('テキスト', {
                left: pointer.x,
                top: pointer.y,
                fontSize: 32,
                fill: currentColor.value
            })

            canvas.add(text)
            canvas.setActiveObject(text)
            canvas.renderAll()

            return
        }

    })

    // =========================
    // Deleteキー
    // =========================
    handleKeydown = (e) => {

        if (e.key !== 'Delete' && e.key !== 'Backspace') return

        const active = canvas.getActiveObject()

        if (!active) return

        if (active.type === 'activeSelection') {

            active.forEachObject((obj) => {
                canvas.remove(obj)
            })

        } else {

            canvas.remove(active)

        }

        canvas.discardActiveObject()
        canvas.renderAll()

    }

    document.addEventListener('keydown', handleKeydown)

    // =========================
    // 読み込み
    // =========================
    window.loadCanvas = () => {

        const json = localStorage.getItem('canvas')

        if (!json) return

        canvas.loadFromJSON(JSON.parse(json), () => {
            canvas.renderAll()
        })

    }

})

// =========================
// ツール切替
// =========================
watch(currentTool, (tool) => {
    if (!canvas) return
    if (tool === 'pen') {
        canvas.isDrawingMode = true
        canvas.freeDrawingBrush.color = currentColor.value
    } else {
        canvas.isDrawingMode = false
    }
})

// =========================
// 色の切替
// =========================

watch(currentColor, (color) => {
    if (!canvas) return
    if (canvas.freeDrawingBrush) {
        canvas.freeDrawingBrush.color = color
    }

})

// =========================
// 終了処理
// =========================
onUnmounted(() => {

    if (handleKeydown) {
        document.removeEventListener('keydown', handleKeydown)
    }

    if (canvas) {
        canvas.dispose()
        canvas = null
    }

})
</script>

<style scoped>
.canvas-page {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 20px;
}

.toolbar {
    display: flex;
    gap: 10px;
}

.toolbar button {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    background: #3b82f6;
    color: white;
    cursor: pointer;
    font-size: 15px;
}

.toolbar button:hover {
    background: #2563eb;
}

.toolbar button.active {
    background: #1d4ed8;
}

.color-picker {
    display: flex;
    gap: 8px;
    align-items: center;
}

.color {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #ddd;
    cursor: pointer;
}


canvas {
    border: 1px solid #ccc;
    background: white;
}
</style>