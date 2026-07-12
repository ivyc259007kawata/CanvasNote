<template>
    <div class="canvas-page">

        <CanvasToolbar :tool="state.tool" :color="state.color" @update:tool="state.tool = $event"
            @update:color="state.color = $event" @image="image.openImage" @undo="history.undo" @redo="history.redo"
            @open="openCanvas" @save="save.openSaveDialog" />

        <input ref="fileInput" type="file" accept=".canvas,.json" hidden @change="save.loadCanvasFile" />
        <input ref="imageInput" type="file" accept="image/*" hidden @change="image.loadImageFromFile" />

        <!-- 保存ダイアログ -->
        <SaveDialog :show="save.showSaveDialog" @canvas="save.saveAsCanvas" @png="() => save.saveAsImage('png')"
            @jpg="() => save.saveAsImage('jpeg')" @close="save.closeSaveDialog" />

        <div class="layout">
            <CanvasArea :canvasEl="canvasEl" />

            <PropertyPanel :activeObject="panel.activeObject" :fillColor="panel.fillColor" :left="panel.left"
                :top="panel.top" :objectWidth="panel.objectWidth" :objectHeight="panel.objectHeight"
                :angle="panel.angle" @update:fillColor="panel.fillColor = $event" @update:left="panel.left = $event"
                @update:top="panel.top = $event" @update:objectWidth="panel.objectWidth = $event"
                @update:objectHeight="panel.objectHeight = $event" @update:angle="panel.angle = $event"
                @front="panel.bringToFront" @back="panel.sendToBack" @delete="panel.deleteObject" />
        </div>

    </div>
</template>

<script setup>

import { ref, reactive, watch, onMounted, onUnmounted } from 'vue'

import CanvasToolbar from './CanvasToolbar.vue'
import CanvasArea from './CanvasArea.vue'
import SaveDialog from './SaveDialog.vue'
import PropertyPanel from './PropertyPanel.vue'

import { useCanvas } from '@/composables/useCanvas'
import { useHistory } from '@/composables/useHistory'
import { usePropertyPanel } from '@/composables/usePropertyPanel'
import { useCanvasEvents } from '@/composables/useCanvasEvents'
import { useKeyboard } from '@/composables/useKeyboard'
import { useClipboard } from '@/composables/useClipboard'
import { useImage } from '@/composables/useImage'
import { useSaveLoad } from '@/composables/useSaveLoad'

/* =========================
  refs
========================= */
const canvasEl = ref(null)
const fileInput = ref(null)
const imageInput = ref(null)

/* =========================
  core canvas
========================= */
const canvas = useCanvas(canvasEl)

/* =========================
  UI state
========================= */
const state = reactive({
    tool: 'select',
    color: '#000000'
})

/* =========================
  composables
========================= */

const canvasManager = useCanvas(canvasEl)

const history = useHistory(canvasManager)
const panel = usePropertyPanel(canvasManager, history.saveHistory)
const clipboard = useClipboard()
const image = useImage(canvasManager, imageInput, history.saveHistory, panel.updateActiveObject)
const save = useSaveLoad(canvasManager)
const events = useCanvasEvents(canvasManager, state, panel, history.saveHistory)
const keyboard = useKeyboard(canvasManager, history.saveHistory, clipboard)
/* =========================
  file operations
========================= */
const openCanvas = () => fileInput.value?.click()


/* =========================
  lifecycle
========================= */
onMounted(() => {
    // =========================
    // Fabric Canvas生成
    // =========================
    canvas.initCanvas()

    // =========================
    // パネル監視開始
    // =========================
    panel.startWatchers(watch)

    // =========================
    // Fabricイベント登録
    // =========================
    panel.bindCanvasEvents()
    events.bindEvents()

    // =========================
    // キーボード操作
    // =========================
    keyboard.bindKeyboard()

    // =========================
    // 初期オブジェクト追加
    // =========================
    canvas.addDefaultRect()

    // =========================
    // 初期履歴保存
    // =========================
    history.init()


})

onUnmounted(() => {
    keyboard.unbindKeyboard()
    events.unbindEvents()
    canvas.destroyCanvas()
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


/* レイヤーボタン */
.layer-button {
    background: #3b82f6;
}

.layer-button:hover {
    background: #2563eb;
}

/* 削除ボタン */
.delete-button {
    background: #e53935;
}

.delete-button:hover {
    background: #c62828;
}
</style>