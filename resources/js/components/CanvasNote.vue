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

        <input ref="fileInput" type="file" accept=".canvas,.json" style="display:none" @change="loadCanvasFile" />

        <!-- キャンバス本体とプロパティパネルを横並びに -->
        <div class="layout">
            <canvas ref="canvasEl" width="1000" height="600"></canvas>

            <div class="property-panel">
                <h3>プロパティ</h3>

                <div v-if="activeObject">
                    <p>タイプ: {{ activeObject.type }}</p>

                    <label>色</label>
                    <input type="color" v-model="fillColor" />

                    <label>X</label>
                    <input type="number" v-model.number="left" />

                    <label>Y</label>
                    <input type="number" v-model.number="top" />

                    <button @click="deleteObject">削除</button>

                    <button @click="bringToFront">
                        ⬆ 最前面へ
                    </button>

                    <button @click="sendToBack">
                        ⬇ 最背面へ
                    </button>

                    <button @click="deleteObject">
                        🗑 削除
                    </button>
                </div>

                <div v-else>
                    未選択
                </div>
            </div>
        </div>

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

// プロパティパネル用の値
// activeObjectが切り替わったタイミングで、選択中オブジェクトの
// 現在値で初期化し、入力されたらFabricオブジェクト側へ書き戻す
const fillColor = ref('#000000')
const left = ref(0)
const top = ref(0)

// fillColor/left/top の watch がプログラム的な代入（選択切替時の初期化）
// に反応して無駄に saveHistory を呼ばないようにするためのフラグ
let isSyncingFromObject = false

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

// 連続的な変化（ドラッグ中の位置・色のスライダー操作など）で
// saveHistory が大量に呼ばれても、操作が止まってから一定時間後に
// 最後の状態だけを1件として記録する（デバウンス）
let saveHistoryTimer = null

const saveHistory = () => {

    if (isRestoring) return
    if (!canvas) return

    if (saveHistoryTimer) {
        clearTimeout(saveHistoryTimer)
    }

    saveHistoryTimer = setTimeout(() => {

        saveHistoryTimer = null

        const json = JSON.stringify(canvas.toJSON())

        if (history[historyIndex] === json) return

        history.splice(historyIndex + 1)

        history.push(json)

        historyIndex = history.length - 1

    }, 300)
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

// =========================
// プロパティパネルの選択オブジェクト同期
// =========================

// 選択中オブジェクトが変わったら、パネルの入力欄を
// そのオブジェクトの現在値で初期化する。
// 引数を渡さない場合は activeObject.value を対象にする
// （選択変更イベントなど、targetを直接持たない呼び出し元のため）
const syncPanelFromObject = (obj = activeObject.value) => {

    if (!obj) return

    isSyncingFromObject = true

    fillColor.value = obj.fill || '#000000'
    left.value = Math.round(obj.left)
    top.value = Math.round(obj.top)

    isSyncingFromObject = false
}

const updateActiveObject = () => {
    activeObject.value = canvas.getActiveObject()
    syncPanelFromObject()
}

const clearActiveObject = () => {
    activeObject.value = null

    fillColor.value = '#000000'
    left.value = 0
    top.value = 0
}

const deleteObject = () => {

    if (!activeObject.value) return

    canvas.remove(activeObject.value)
    canvas.discardActiveObject()
    canvas.renderAll()

    clearActiveObject()
}

const bringToFront = () => {

    if (!activeObject.value) return

    canvas.bringObjectToFront(activeObject.value)

    canvas.renderAll()

    saveHistory()
}

watch(fillColor, (color) => {
    if (isSyncingFromObject) return
    if (!activeObject.value) return

    activeObject.value.set('fill', color)
    canvas.renderAll()
    saveHistory()
}, { flush: 'sync' })

// X座標が変更されたら、Fabricオブジェクトに反映
watch(left, (value) => {
    if (isSyncingFromObject) return
    if (!activeObject.value) return
    if (Number.isNaN(value)) return

    activeObject.value.set('left', value)
    activeObject.value.setCoords()
    canvas.renderAll()
    saveHistory()
}, { flush: 'sync' })

// Y座標が変更されたら、Fabricオブジェクトに反映
watch(top, (value) => {
    if (isSyncingFromObject) return
    if (!activeObject.value) return
    if (Number.isNaN(value)) return

    activeObject.value.set('top', value)
    activeObject.value.setCoords()
    canvas.renderAll()
    saveHistory()
}, { flush: 'sync' })

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

    // 選択系イベント
    canvas.on('selection:created', updateActiveObject)
    canvas.on('selection:updated', updateActiveObject)
    canvas.on('selection:cleared', clearActiveObject)

    // キャンバス上で直接操作した場合も
    // パネルの表示を最新に同期する
    canvas.on('object:moving', (e) => syncPanelFromObject(e.target))
    canvas.on('object:scaling', (e) => syncPanelFromObject(e.target))
    canvas.on('object:rotating', (e) => syncPanelFromObject(e.target))
    canvas.on('object:modified', (e) => syncPanelFromObject(e.target))




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

/* レイアウト：キャンバスとプロパティパネルを横並びに */
.layout {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

canvas {
    border: 1px solid #ccc;
    background: white;
}

.property-panel {
    width: 220px;
    padding: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fafafa;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.property-panel h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
}

.property-panel label {
    font-size: 13px;
    color: #555;
    margin-top: 6px;
}

.property-panel input[type="number"] {
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.property-panel input[type="color"] {
    width: 100%;
    height: 32px;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 2px;
}

.property-panel button {
    margin-top: 12px;
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    background: #e53935;
    color: white;
    cursor: pointer;
}

.property-panel button:hover {
    background: #c62828;
}
</style>