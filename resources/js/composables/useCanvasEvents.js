import { Rect, IText } from 'fabric'
import { watch } from 'vue'

export function useCanvasEvents(
    canvas,
    state,
    panel,
    saveHistory,
    updateThumbnail
) {

    const fabricCanvas = () => {
        return canvas.value
    }

    // =====================
    // 消しゴム
    // =====================

    let isErasing = false
    let erasedSomething = false

    // 消しゴム開始
    const startErase = () => {
        isErasing = true
        erasedSomething = false
    }

    // 消しゴム移動
    const moveErase = (event) => {

        if (!isErasing) return

        const fc = fabricCanvas()
        if (!fc) return

        const result = fc.findTarget(event.e)

        if (!result) return

        const target = result.target

        if (!target) return

        // ペン・マーカーの線だけ削除
        if (target.type === 'path') {

            fc.remove(target)

            erasedSomething = true

            fc.requestRenderAll()
        }
    }

    // 消しゴム終了
    const endErase = () => {

        if (!isErasing) return

        isErasing = false

        if (erasedSomething) {

            saveHistory()

            updateThumbnail()
        }
    }

    // =====================
    // パス作成
    // =====================

    const onPathCreated = () => {

        saveHistory()

        updateThumbnail()
    }

    // =====================
    // マウスダウン
    // =====================

    const onMouseDown = (event) => {

        const fc = fabricCanvas()

        if (!fc) return

        const tool = state.tool

        // =====================
        // 消しゴム
        // =====================

        if (tool === 'eraser') {

            fc.isDrawingMode = false

            fc.skipTargetFind = false

            startErase()

            // クリックした瞬間にも
            // 線の上なら消せる
            moveErase(event)

            return
        }

        // =====================
        // 選択
        // =====================

        if (tool === 'select') {
            return
        }

        // =====================
        // ペン・マーカー
        // =====================

        if (
            tool === 'pen' ||
            tool === 'marker'
        ) {

            fc.isDrawingMode = true

            return
        }

        // =====================
        // 描画モード解除
        // =====================

        fc.isDrawingMode = false

        const pointer = fc.getScenePoint(event.e)

        // =====================
        // 四角
        // =====================

        if (tool === 'rectangle') {

            const rect = new Rect({
                left: pointer.x,
                top: pointer.y,
                width: 120,
                height: 120,
                fill: state.color
            })

            fc.add(rect)

            fc.setActiveObject(rect)

            panel.updateActiveObject()

            fc.requestRenderAll()

            saveHistory()

            updateThumbnail()

            // 追加後は選択ツール
            state.tool = 'select'

            return
        }

        // =====================
        // テキスト
        // =====================

        if (tool === 'text') {

            const text = new IText(
                'テキスト',
                {
                    left: pointer.x,
                    top: pointer.y,
                    fontSize: 30,
                    fill: state.color
                }
            )

            fc.add(text)

            fc.setActiveObject(text)

            panel.updateActiveObject()

            fc.requestRenderAll()

            saveHistory()

            updateThumbnail()

            // 追加後は選択ツール
            state.tool = 'select'

            // そのまま文字入力
            text.enterEditing()

            text.selectAll()

            return
        }
    }

    // =====================
    // イベント登録
    // =====================

    let stopToolWatch = null

    const bindEvents = () => {

        const fc = fabricCanvas()

        if (!fc) return

        fc.selection = true

        // マウスダウン
        fc.on(
            'mouse:down',
            onMouseDown
        )

        // マウス移動
        fc.on(
            'mouse:move',
            moveErase
        )

        // マウスアップ
        fc.on(
            'mouse:up',
            endErase
        )

        // ペン・マーカー
        fc.on(
            'path:created',
            onPathCreated
        )

        // =====================
        // ツール変更監視
        // =====================

        stopToolWatch = watch(
            () => state.tool,
            (tool) => {

                const current = fabricCanvas()

                if (!current) return

                current.isDrawingMode =
                    tool === 'pen' ||
                    tool === 'marker'

                // 消しゴムでは
                // オブジェクト選択を無効化
                current.selection =
                    tool !== 'eraser'
            }
        )
    }

    // =====================
    // イベント解除
    // =====================

    const unbindEvents = () => {

        const fc = fabricCanvas()

        if (!fc) return

        fc.off(
            'mouse:down',
            onMouseDown
        )

        fc.off(
            'mouse:move',
            moveErase
        )

        fc.off(
            'mouse:up',
            endErase
        )

        fc.off(
            'path:created',
            onPathCreated
        )

        // watchも解除
        if (stopToolWatch) {

            stopToolWatch()

            stopToolWatch = null
        }
    }

    return {
        bindEvents,
        unbindEvents
    }
}