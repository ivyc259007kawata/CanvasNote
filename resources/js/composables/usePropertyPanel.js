import { ref } from 'vue'


export function usePropertyPanel(canvas, saveHistory) {


    const activeObject = ref(null)


    // =========================
    // 表示用データ
    // =========================

    const fillColor = ref('#000000')
    const left = ref(0)
    const top = ref(0)
    const objectWidth = ref(0)
    const objectHeight = ref(0)
    const angle = ref(0)



    // Fabric Canvas取得
    const fabricCanvas = () => {
        return canvas.value
    }



    let isSyncingFromObject = false



    // =========================
    // オブジェクト → パネル
    // =========================

    const syncPanelFromObject = (obj = activeObject.value) => {

        if (!obj) return


        isSyncingFromObject = true


        fillColor.value = obj.fill || '#000000'

        left.value = Math.round(obj.left ?? 0)

        top.value = Math.round(obj.top ?? 0)

        objectWidth.value =
            Math.round(obj.getScaledWidth())

        objectHeight.value =
            Math.round(obj.getScaledHeight())

        angle.value =
            Math.round(obj.angle ?? 0)



        isSyncingFromObject = false

    }



    // =========================
    // 選択更新
    // =========================

    const updateActiveObject = () => {

        const fc = fabricCanvas()

        if (!fc) return


        activeObject.value =
            fc.getActiveObject()


        syncPanelFromObject()

    }



    const clearActiveObject = () => {

        activeObject.value = null

        fillColor.value = '#000000'
        left.value = 0
        top.value = 0
        objectWidth.value = 0
        objectHeight.value = 0
        angle.value = 0

    }



    // =========================
    // 削除
    // =========================

    const deleteObject = () => {

        const fc = fabricCanvas()

        if (!fc) return


        const active =
            fc.getActiveObject()


        if (!active) return



        fc.remove(active)

        fc.discardActiveObject()

        fc.renderAll()


        clearActiveObject()

        saveHistory()

    }



    // =========================
    // レイヤー操作
    // =========================

    const bringToFront = () => {

        const fc = fabricCanvas()

        if (!fc) return


        if (!activeObject.value) return


        fc.bringObjectToFront(
            activeObject.value
        )

        fc.renderAll()

        saveHistory()

    }



    const sendToBack = () => {

        const fc = fabricCanvas()

        if (!fc) return


        if (!activeObject.value) return


        fc.sendObjectToBack(
            activeObject.value
        )

        fc.renderAll()

        saveHistory()

    }



    // =========================
    // input変更監視
    // =========================

    const startWatchers = (watch) => {


        watch(fillColor, (value) => {

            if (isSyncingFromObject) return
            if (!activeObject.value) return


            activeObject.value.set(
                'fill',
                value
            )

            fabricCanvas().renderAll()

            saveHistory()

        }, { flush: 'sync' })



        watch(left, (value) => {

            if (isSyncingFromObject) return
            if (!activeObject.value) return


            activeObject.value.set(
                'left',
                value
            )

            activeObject.value.setCoords()

            fabricCanvas().renderAll()

            saveHistory()

        }, { flush: 'sync' })



        watch(top, (value) => {

            if (isSyncingFromObject) return
            if (!activeObject.value) return


            activeObject.value.set(
                'top',
                value
            )

            activeObject.value.setCoords()

            fabricCanvas().renderAll()

            saveHistory()

        }, { flush: 'sync' })



        watch(objectWidth, (value) => {

            if (isSyncingFromObject) return
            if (!activeObject.value) return


            if (!activeObject.value.width)
                return


            activeObject.value.scaleX =
                value / activeObject.value.width


            activeObject.value.setCoords()

            fabricCanvas().renderAll()

            saveHistory()

        }, { flush: 'sync' })



        watch(objectHeight, (value) => {

            if (isSyncingFromObject) return
            if (!activeObject.value) return


            if (!activeObject.value.height)
                return


            activeObject.value.scaleY =
                value / activeObject.value.height


            activeObject.value.setCoords()

            fabricCanvas().renderAll()

            saveHistory()

        }, { flush: 'sync' })



        watch(angle, (value) => {

            if (isSyncingFromObject) return
            if (!activeObject.value) return


            activeObject.value.rotate(value)

            activeObject.value.setCoords()

            fabricCanvas().renderAll()

            saveHistory()

        }, { flush: 'sync' })


    }



    // =========================
    // Fabricイベント
    // =========================

    const bindCanvasEvents = () => {

        const fc = fabricCanvas()

        if (!fc) return

        fc.on(
            'selection:created',
            updateActiveObject
        )

        fc.on(
            'selection:updated',
            updateActiveObject
        )

        fc.on(
            'selection:cleared',
            clearActiveObject
        )

        const syncFromTarget = (e) => {

            if (e.target) {

                syncPanelFromObject(
                    e.target
                )

            }

        }

        fc.on(
            'object:moving',
            syncFromTarget
        )

        fc.on(
            'object:scaling',
            syncFromTarget
        )

        fc.on(
            'object:rotating',
            syncFromTarget
        )

        fc.on(
            'object:modified',
            () => {

                syncPanelFromObject(
                    fc.getActiveObject()
                )

                saveHistory()

            }
        )

    }



    return {

        activeObject,

        fillColor,
        left,
        top,
        objectWidth,
        objectHeight,
        angle,


        updateActiveObject,
        clearActiveObject,

        deleteObject,

        bringToFront,
        sendToBack,

        startWatchers,

        bindCanvasEvents

    }

}