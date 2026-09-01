<template>
    <div class="canvas-page">

        <!-- Toolbar -->
        <CanvasToolbar :tool="state.tool" :color="state.color" :brushWidth="state.brushWidth" @update:tool="(tool) => {

            state.tool = tool

            canvas.setTool(tool)

        }" @update:color="(color) => {

            state.color = color

            canvas.setBrushColor(color)

        }" @update:brushWidth="(width) => {

            state.brushWidth = width

            canvas.setBrushWidth(width)

        }" @image="image.openImage" @undo="history.undo" @redo="history.redo" @open="openCanvas"
            @save="save.openSaveDialog" @lesson-save="saveLesson" />


        <!-- File -->
        <input ref="fileInput" type="file" accept=".canvas,.json" hidden @change="save.loadCanvasFile" />

        <input ref="imageInput" type="file" accept="image/*" hidden @change="image.loadImageFromFile" />



        <!-- Save Dialog -->
        <SaveDialog :show="save.showSaveDialog.value" @canvas="save.saveAsCanvas" @png="() => save.saveAsImage('png')"
            @jpg="() => save.saveAsImage('jpeg')" @close="save.closeSaveDialog" />

        <PageTabs :pages="props.lesson.pages" :currentPage="pages.currentPage.value" @change="pages.changePage"
            @add="pages.addPage" @delete="pages.deletePage" @rename="pages.renamePage" @move="pages.movePage" />

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
    onUnmounted,
    computed
} from 'vue'

const props = defineProps({
    lesson: Object
})

const lesson = computed(() => props.lesson)


import CanvasToolbar from './CanvasToolbar.vue'
import PageTabs from './PageTabs.vue'
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
import { useLessonPages } from '@/composables/useLessonPages'
import { useAutoSave } from '@/composables/useAutoSave'


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

    color: '#000000',

    brushWidth: 5

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



const pages =
    useLessonPages(
        lesson,
        canvas,
        history
    )


const updateThumbnail = () => {

    const page =
        props.lesson?.pages?.[pages.currentPage.value]


    if (!page) return


    page.thumbnail =
        canvas.canvas.value.toDataURL({

            format: 'png',

            quality: 0.5,

            multiplier: 0.2

        })

}



const events =
    useCanvasEvents(
        canvas.canvas,
        state,
        panel,
        history.saveHistory,
        updateThumbnail
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


    // 初期ページ読み込み
    if (props.lesson?.pages?.length) {


        pages.loadCurrentPage()


    }
    else {


        canvas.addDefaultRect()

        history.init()


    }


    // 自動保存開始
    autoSave.start()


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
| Lesson Save
|--------------------------------------------------------------------------
*/

function saveLesson(showMessage = true) {

    if (!props.lesson) return

    pages.saveCurrentPage()


    const lessons =
        JSON.parse(
            localStorage.getItem('lessons') || '[]'
        )


    const index =
        lessons.findIndex(
            item => item.id === props.lesson.id
        )


    if (index !== -1) {

        lessons[index] = props.lesson

    }


    localStorage.setItem(
        'lessons',
        JSON.stringify(lessons)
    )


    if (showMessage) {

        alert('教材を保存しました')

    }

}

const autoSave =
    useAutoSave(
        lesson,
        pages,
        saveLesson
    )

const emit = defineEmits([
    'save-status'
])

watch(
    autoSave.saveStatus,
    (status) => {

        emit(
            'save-status',
            status
        )

    }
)

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

onUnmounted(() => {

    autoSave.stop()

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