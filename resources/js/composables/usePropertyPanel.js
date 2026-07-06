import { ref } from 'vue'

/**
 * プロパティパネル（選択中オブジェクトの色・位置・サイズ・回転）を管理するcomposable
 *
 * @param {import('vue').Ref} canvas - Fabric Canvasインスタンスを持つref
 * @param {Function} saveHistory - 変更のたびに履歴保存するための関数
 */
export function usePropertyPanel(canvas, saveHistory) {

    const activeObject = ref(null)

    // プロパティパネル用の値
    // activeObjectが切り替わったタイミングで、選択中オブジェクトの
    // 現在値で初期化し、入力されたらFabricオブジェクト側へ書き戻す
    const fillColor = ref('#000000')
    const left = ref(0)
    const top = ref(0)
    const objectWidth = ref(0)
    const objectHeight = ref(0)
    const angle = ref(0)

    // fillColor/left/top の watch がプログラム的な代入（選択切替時の初期化）
    // に反応して無駄に saveHistory を呼ばないようにするためのフラグ
    let isSyncingFromObject = false

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
        objectWidth.value = Math.round(obj.getScaledWidth())
        objectHeight.value = Math.round(obj.getScaledHeight())
        angle.value = Math.round(obj.angle || 0)

        isSyncingFromObject = false
    }


    const updateActiveObject = () => {
        activeObject.value = canvas.value.getActiveObject()
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

    const deleteObject = () => {
        const active = canvas.value.getActiveObject()
        if (!active) return

        if (active.type === 'activeSelection' || active.type === 'group') {
            active.forEachObject((obj) => {
                canvas.value.remove(obj)
            })
        } else {
            canvas.value.remove(active)
        }

        canvas.value.discardActiveObject()
        canvas.value.renderAll()
        clearActiveObject()
    }

    // 最前面
    const bringToFront = () => {
        if (!activeObject.value) return

        canvas.value.bringObjectToFront(activeObject.value)
        canvas.value.renderAll()
        saveHistory()
    }

    // 最背面
    const sendToBack = () => {
        if (!activeObject.value) return

        canvas.value.sendObjectToBack(activeObject.value)
        canvas.value.renderAll()
        saveHistory()
    }

    // watch群はcomposable内部で完結させる。
    // 呼び出し側(CanvasNote.vue)からはimportして呼ぶだけでよい
    const startWatchers = (watch) => {

        watch(fillColor, (color) => {
            if (isSyncingFromObject) return
            if (!activeObject.value) return

            activeObject.value.set('fill', color)
            canvas.value.renderAll()
            saveHistory()
        }, { flush: 'sync' })

        // X座標が変更されたら、Fabricオブジェクトに反映
        watch(left, (value) => {
            if (isSyncingFromObject) return
            if (!activeObject.value) return
            if (Number.isNaN(value)) return

            activeObject.value.set('left', value)
            activeObject.value.setCoords()
            canvas.value.renderAll()
            saveHistory()
        }, { flush: 'sync' })

        // Y座標が変更されたら、Fabricオブジェクトに反映
        watch(top, (value) => {
            if (isSyncingFromObject) return
            if (!activeObject.value) return
            if (Number.isNaN(value)) return

            activeObject.value.set('top', value)
            activeObject.value.setCoords()
            canvas.value.renderAll()
            saveHistory()
        }, { flush: 'sync' })

        // 幅が変更されたら、scaleXを再計算してFabricオブジェクトに反映
        // getScaledWidth()で表示しているため、書き戻しもスケール経由で行う
        watch(objectWidth, (value) => {
            if (isSyncingFromObject) return
            if (!activeObject.value) return
            if (Number.isNaN(value)) return
            if (!activeObject.value.width) return  // 0除算防止

            activeObject.value.scaleX = value / activeObject.value.width
            activeObject.value.setCoords()
            canvas.value.renderAll()
            saveHistory()
        }, { flush: 'sync' })

        // 高さが変更されたら、scaleYを再計算してFabricオブジェクトに反映
        watch(objectHeight, (value) => {
            if (isSyncingFromObject) return
            if (!activeObject.value) return
            if (Number.isNaN(value)) return
            if (!activeObject.value.height) return  // 0除算防止

            activeObject.value.scaleY = value / activeObject.value.height
            activeObject.value.setCoords()
            canvas.value.renderAll()
            saveHistory()
        }, { flush: 'sync' })

        // 回転角度が変更されたら、Fabricオブジェクトに反映
        watch(angle, (value) => {
            if (isSyncingFromObject) return
            if (!activeObject.value) return
            if (Number.isNaN(value)) return

            activeObject.value.rotate(value)
            activeObject.value.setCoords()
            canvas.value.renderAll()
            saveHistory()
        }, { flush: 'sync' })
    }

    const bindCanvasEvents = () => {

        canvas.value.on('selection:created', updateActiveObject)
        canvas.value.on('selection:updated', updateActiveObject)
        canvas.value.on('selection:cleared', clearActiveObject)

        // 移動・変形系は全部 target を使う
        const syncFromTarget = (e) => {
            if (e.target) {
                syncPanelFromObject(e.target)
            }
        }

        canvas.value.on('object:moving', syncFromTarget)
        canvas.value.on('object:scaling', syncFromTarget)
        canvas.value.on('object:rotating', syncFromTarget)

        // modified だけ例外（activeSelection対策）
        canvas.value.on('object:modified', () => {
            const obj = canvas.value.getActiveObject()
            syncPanelFromObject(obj)
        })
    }



    return {
        activeObject,
        fillColor,
        left,
        top,
        objectWidth,
        objectHeight,
        angle,
        syncPanelFromObject,
        updateActiveObject,
        clearActiveObject,
        deleteObject,
        bringToFront,
        sendToBack,
        startWatchers,
        bindCanvasEvents
    }
}