import { Rect, IText } from 'fabric'

export function useCanvasEvents(canvas, state, panel, saveHistory) {

    const onMouseDown = (opt) => {
        if (!canvas.value) return

        const tool = state.tool.value

        // =========================
        // 選択モード
        // =========================
        if (tool === 'select') {
            if (opt.target) {
                canvas.value.setActiveObject(opt.target)
                panel.updateActiveObject?.()
                canvas.value.renderAll()
            }
            return
        }

        // =========================
        // ペンモードは何もしない
        // =========================
        if (tool === 'pen') {
            return
        }

        const pointer = canvas.value.getScenePoint(opt.e)

        // =========================
        // 四角
        // =========================
        if (tool === 'rectangle') {

            if (opt.target) {
                canvas.value.setActiveObject(opt.target)
                panel.updateActiveObject?.()
                canvas.value.renderAll()
                return
            }

            const rect = new Rect({
                left: pointer.x,
                top: pointer.y,
                width: 120,
                height: 120,
                fill: state.color.value
            })

            canvas.value.add(rect)
            canvas.value.setActiveObject(rect)
            canvas.value.renderAll()

            saveHistory()
            return
        }

        // =========================
        // テキスト
        // =========================
        if (tool === 'text') {

            if (opt.target) {
                canvas.value.setActiveObject(opt.target)
                panel.updateActiveObject?.()
                canvas.value.renderAll()
                return
            }

            const text = new IText('テキスト', {
                left: pointer.x,
                top: pointer.y,
                fontSize: 32,
                fill: state.color.value
            })

            canvas.value.add(text)
            canvas.value.setActiveObject(text)
            canvas.value.renderAll()

            saveHistory()
        }
    }

    const bindEvents = () => {
        canvas.value.on('mouse:down', onMouseDown)
    }

    const unbindEvents = () => {
        canvas.value.off('mouse:down', onMouseDown)
    }

    return {
        bindEvents,
        unbindEvents
    }
}