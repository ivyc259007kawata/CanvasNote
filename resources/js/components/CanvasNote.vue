<template>

    <div class="canvas-page">

        <div class="toolbar">
            <button>🖱 選択</button>
            <button>▭ 四角</button>
            <button>📝 テキスト</button>
            <button>✏ ペン</button>
            <button>💾 保存</button>
        </div>

        <canvas ref="canvasEl" width="1000" height="600">
        </canvas>

    </div>

</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { Canvas, Rect } from 'fabric'

const canvasEl = ref(null)
const currentTool = ref('select')

let canvas = null
let handleKeydown = null

onMounted(() => {
    console.log('mounted')

    if (!canvasEl.value) {
        console.log('canvas null')
        return
    }

    // =========================
    // Fabric 初期化
    // =========================
    canvas = new Canvas(canvasEl.value, {
        width: 1000,
        height: 600,
        backgroundColor: '#ffffff',
        selection: true
    })

    window.canvas = canvas

    canvas.isDrawingMode = false
    canvas.renderAll()

    // =========================
    // 初期オブジェクト
    // =========================
    canvas.add(
        new Rect({
            left: 100,
            top: 100,
            width: 120,
            height: 120,
            fill: `hsl(${Math.random() * 360}, 70%, 60%)`
        })
    )

    // =========================
    // クリックで四角追加（空白のみ）
    // =========================
    canvas.on('mouse:down', (opt) => {
        if (opt.target) return

        const pointer = canvas.getScenePoint(opt.e)

        const rect = new Rect({
            left: pointer.x,
            top: pointer.y,
            width: 100,
            height: 100,
            fill: 'red'
        })

        canvas.add(rect)
        canvas.setActiveObject(rect)
        canvas.renderAll()
    })

    // =========================
    // キー削除（安定版）
    // =========================
    handleKeydown = (e) => {
        if (e.key !== 'Delete' && e.key !== 'Backspace') return

        const active = canvas.getActiveObject()

        console.log('active:', active)

        if (!active) return

        // 複数選択対応（Fabric v7）
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
    // 保存（テスト）
    // =========================
    window.saveCanvas = () => {
        const json = canvas.toJSON()
        localStorage.setItem('canvas', JSON.stringify(json))
        console.log('saved')
    }

    // =========================
    // 読み込み（テスト）
    // =========================
    window.loadCanvas = () => {
        const json = localStorage.getItem('canvas')
        if (!json) return

        canvas.loadFromJSON(JSON.parse(json), () => {
            canvas.renderAll()
            console.log('loaded')
        })
    }
})

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

canvas {
    border: 1px solid #ccc;
    background: white;
}
</style>