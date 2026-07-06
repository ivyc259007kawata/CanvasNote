<template>
  <div class="canvas-page">

    <CanvasToolbar
      :tool="state.tool"
      @update:tool="state.tool = $event"
      @update:color="state.color = $event"
      @image="image.openImage"
      @undo="history.undo"
      @redo="history.redo"
      @open="openCanvas"
      @save="openSaveDialog"
    />

    <input ref="fileInput" type="file" accept=".canvas,.json" hidden @change="loadCanvasFile" />
    <input ref="imageInput" type="file" accept="image/*" hidden />

    <!-- 保存ダイアログ -->
    <div v-if="showSaveDialog" class="dialog-overlay" @click.self="closeSaveDialog">
      <div class="dialog-box">
        <h3>保存形式を選択</h3>

        <button @click="save.saveAsCanvas()">.canvas</button>
        <button @click="save.saveAsImage('png')">.png</button>
        <button @click="save.saveAsImage('jpeg')">.jpg</button>

        <button @click="closeSaveDialog">キャンセル</button>
      </div>
    </div>

    <div class="layout">
      <canvas ref="canvasEl" width="1000" height="600"></canvas>

      <div class="property-panel">
        <h3>プロパティ</h3>

        <div v-if="panel.activeObject">
          <p>タイプ: {{ panel.activeObject.type }}</p>

          <label>色</label>
          <input type="color" v-model="panel.fillColor" />

          <label>X</label>
          <input type="number" v-model.number="panel.left" />

          <label>Y</label>
          <input type="number" v-model.number="panel.top" />

          <label>幅</label>
          <input type="number" v-model.number="panel.objectWidth" />

          <label>高さ</label>
          <input type="number" v-model.number="panel.objectHeight" />

          <label>回転</label>
          <input type="number" v-model.number="panel.angle" />

          <button @click="panel.bringToFront()">⬆ 最前面</button>
          <button @click="panel.sendToBack()">⬇ 最背面</button>
          <button @click="panel.deleteObject()">🗑 削除</button>
        </div>

        <div v-else>未選択</div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'

import CanvasToolbar from './CanvasToolbar.vue'

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
const history = useHistory(canvas)
const panel = usePropertyPanel(canvas, history.saveHistory)
const clipboard = useClipboard(canvas, history.saveHistory)
const image = useImage(canvas, imageInput, history.saveHistory)
const save = useSaveLoad(canvas)
const events = useCanvasEvents(canvas, state, panel, history.saveHistory)
const keyboard = useKeyboard(canvas, history.saveHistory, clipboard)

/* =========================
  dialog
========================= */
const showSaveDialog = ref(false)

const openSaveDialog = () => (showSaveDialog.value = true)
const closeSaveDialog = () => (showSaveDialog.value = false)

/* =========================
  file operations
========================= */
const openCanvas = () => fileInput.value?.click()

const loadCanvasFile = async (e) => {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return

  const text = await file.text()
  const json = JSON.parse(text)

  await canvas.value.loadFromJSON(json)
  canvas.value.requestRenderAll()

  history.saveHistory()
}

/* =========================
  lifecycle
========================= */
onMounted(() => {
  canvas.initCanvas()

  history.init()

  panel.bind()
  events.bind()
  keyboard.bind()

  canvas.addDefaultRect?.()
})

onUnmounted(() => {
  keyboard.unbind()
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
    width: 100%;
    margin-top: 8px;
    padding: 10px 12px;
    border: none;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
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

/* 保存形式選択ダイアログ */
.dialog-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;

    background: rgba(0, 0, 0, 0.4);

    display: flex;
    align-items: center;
    justify-content: center;

    z-index: 1000;
}

.dialog-box {
    width: 320px;
    padding: 24px;

    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);

    display: flex;
    flex-direction: column;
    gap: 10px;
}

.dialog-box h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
}

.dialog-option {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;

    padding: 12px 14px;

    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fafafa;

    cursor: pointer;
    text-align: left;
}

.dialog-option:hover {
    background: #eef6ff;
    border-color: #93c5fd;
}

.dialog-option-title {
    font-weight: bold;
    font-size: 14px;
    color: #1d4ed8;
}

.dialog-option-desc {
    font-size: 12px;
    color: #666;
}

.dialog-cancel {
    margin-top: 8px;
    padding: 10px;

    border: none;
    border-radius: 8px;
    background: #eee;
    color: #333;

    cursor: pointer;
}

.dialog-cancel:hover {
    background: #ddd;
}
</style>