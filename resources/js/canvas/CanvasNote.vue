<template>
    <div class="canvas-page">

        <!-- Toolbar -->
        <!-- Toolbar -->
        <CanvasToolbar :tool="state.tool" :color="state.color" @update:tool="state.tool = $event" @update:color="(color) => {
            state.color = color
            canvas.setBrushColor(color)
        }" @image="image.openImage" @undo="history.undo" @redo="history.redo" @open="openCanvas"
            @save="save.openSaveDialog" />


        <!-- File -->
        <input ref="fileInput" type="file" accept=".canvas,.json" hidden @change="save.loadCanvasFile" />

        <input ref="imageInput" type="file" accept="image/*" hidden @change="image.loadImageFromFile" />



        <!-- Save Dialog -->
        <SaveDialog :show="save.showSaveDialog.value" @canvas="save.saveAsCanvas" @png="() => save.saveAsImage('png')"
            @jpg="() => save.saveAsImage('jpeg')" @close="save.closeSaveDialog" />



        <div class="layout">


            <!-- Canvas -->
            <CanvasArea :canvas-el="canvasEl" @ready="initCanvas" />



            <!-- Property -->
            <PropertyPanel :activeObject="panel.activeObject.value" :fillColor="panel.fillColor.value"
                :left="panel.left.value" :top="panel.top.value" :objectWidth="panel.objectWidth.value"
                :objectHeight="panel.objectHeight.value" :angle="panel.angle.value" @update:fillColor="
                    panel.fillColor.value = $event
                    " @update:left="
                        panel.left.value = $event
                        " @update:top="
                            panel.top.value = $event
                            " @update:objectWidth="
                                panel.objectWidth.value = $event
                                " @update:objectHeight="
                                    panel.objectHeight.value = $event
                                    " @update:angle="
                                    panel.angle.value = $event
                                    " @front="
                                    panel.bringToFront()
                                    " @back="
                                    panel.sendToBack()
                                    " @delete="
                                    panel.deleteObject()
                                    " />


        </div>

    </div>
</template>



<script setup>

import {
    ref,
    reactive,
    watch,
    onUnmounted
} from 'vue'


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



/*
|--------------------------------------------------------------------------
| refs
|--------------------------------------------------------------------------
*/

const canvasEl = ref(null)

const fileInput = ref(null)

const imageInput = ref(null)

/*
|--------------------------------------------------------------------------
| Canvas
|--------------------------------------------------------------------------
*/

const canvas = useCanvas(canvasEl)



/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const state = reactive({

    tool: 'select',

    color: '#000000'

})

/*
|--------------------------------------------------------------------------
| Composables
|--------------------------------------------------------------------------
*/
const history =
    useHistory(canvas.canvas)

const panel =
    usePropertyPanel(
        canvas.canvas,
        history.saveHistory
    )

const clipboard =
    useClipboard()

const image =
    useImage(
        canvas,
        imageInput,
        history.saveHistory,
        panel.updateActiveObject
    )

const save =
    useSaveLoad(canvas)

const events =
    useCanvasEvents(
        canvas.canvas,
        state,
        panel,
        history.saveHistory
    )

const keyboard =
    useKeyboard(
        canvas,
        history.saveHistory,
        clipboard
    )

/*
|--------------------------------------------------------------------------
| Canvas Initialize
|--------------------------------------------------------------------------
*/

const initCanvas = (el) => {

    // ★ CanvasAreaから渡されたDOM要素をセット
    canvasEl.value = el

    // Fabric Canvas生成
    canvas.initCanvas()

    // Property Panel
    panel.startWatchers(watch)
    panel.bindCanvasEvents()

    // Mouse Event
    events.bindEvents()

    // Keyboard
    keyboard.bindKeyboard()

    // 初期オブジェクト
    canvas.addDefaultRect()

    // History
    history.init()

}

/*
|--------------------------------------------------------------------------
| File
|--------------------------------------------------------------------------
*/
const openCanvas = () => {

    fileInput.value?.click()

}

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

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



.layout {

    display: flex;

    gap: 16px;

    align-items: flex-start;

}



canvas {

    border: 1px solid #ccc;

    background: white;

}
</style>