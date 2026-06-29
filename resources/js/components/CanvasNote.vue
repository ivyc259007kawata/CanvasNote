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

            <button @click="undo">
                ↩ Undo
            </button>

            <button @click="redo">
                ↪ Redo
            </button>

            <button @click="openCanvas">
                📂 開く
            </button>

            <button @click="saveCanvas">
                💾 保存
            </button>
        </div>

        <canvas ref="canvasEl" width="1000" height="600">
        </canvas>

        <input ref="fileInput" type="file" accept=".canvas,.json" style="display:none" @change="loadCanvasFile" />

    </div>

</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { Canvas, Rect, IText, PencilBrush } from 'fabric'

const canvasEl = ref(null)
const currentTool = ref('select')

let canvas = null
let handleKeydown = null
const history = []
let historyIndex = -1
const currentColor = ref('#000000')
const fileInput = ref(null)
const activeObject = ref(null)

// Undo/Redo実行中は object:added などのイベントで
// saveHistory が呼ばれないようにするためのフラグ
let isRestoring = false

// =========================
// 保存
// =========================
const saveCanvas = () => {
    if (!canvas) return

    const json = JSON.stringify(canvas.toJSON())

    const blob = new Blob([json], { type: 'application/json' })
    const url = URL.createObjectURL(blob)

    const a = document.createElement('a')
    a.href = url
    a.download = 'CanvasNote.canvas'
    a.click()

    URL.revokeObjectURL(url)
}

const saveHistory = () => {

    if (isRestoring) return
    if (!canvas) return

    const json = JSON.stringify(canvas.toJSON())

    if (history[historyIndex] === json) return

    history.splice(historyIndex + 1)

    history.push(json)

    historyIndex = history.length - 1
}

const openCanvas = () => {
    fileInput.value.click()
}

const loadCanvasFile = async (event) => {

    const file = event.target.files[0]

    // 同じファイルを連続で選んでも change が発火するようにリセット
    // （input.value をクリアしておかないと2回目の選択が無視される）
    event.target.value = ''

    if (!file) return

    let parsed

    try {
        const text = await file.text()
        parsed = JSON.parse(text)
    } catch (err) {
        console.error('ファイルの読み込みに失敗しました', err)
        alert('このファイルは読み込めませんでした。.canvas または .json 形式のファイルを選んでください。')
        return
    }

    // loadFromJSON はオブジェクトを1つずつ追加するため、
    // isRestoring を立てないと object:added が複数回発火し、
    // 「開く」1回の操作なのに履歴が何件も積まれてしまう。
    // そこで読み込み中は一旦ブロックし、完了後に
    // 「開いた後の状態」をまとめて1件だけ履歴に積む。
    isRestoring = true

    try {
        await canvas.loadFromJSON(parsed)
        canvas.renderAll()
    } finally {
        isRestoring = false
    }

    // 開く操作自体もUndoで戻れるようにする
    // （間違えて別ファイルを開いてしまった場合に備える）
    saveHistory()
}

const undo = async () => {

    if (historyIndex <= 0) return

    historyIndex--

    isRestoring = true

    try {
        await canvas.loadFromJSON(history[historyIndex])
        canvas.renderAll()
    } finally {
        isRestoring = false
    }

}

const redo = async () => {

    if (historyIndex >= history.length - 1) return

    historyIndex++

    isRestoring = true

    try {
        await canvas.loadFromJSON(history[historyIndex])
        canvas.renderAll()
    } finally {
        isRestoring = false
    }

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

    canvas.on('object:added', saveHistory)
    canvas.on('object:modified', saveHistory)
    canvas.on('object:removed', saveHistory)




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

    // object:added で saveHistory がすでに呼ばれているので
    // ここでの明示的な呼び出しは不要（重複防止）

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