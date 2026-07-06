import { ActiveSelection } from 'fabric'

export function useKeyboard(canvas, saveHistory, clipboard) {

    let keyHandler = null

    const onKeydown = (e) => {
        if (!canvas.value) return

        const active = canvas.value.getActiveObject()

        // =========================
        // コピー
        // =========================
        if (e.ctrlKey && e.key.toLowerCase() === 'c') {
            if (!active) return

            active.clone().then((cloned) => {
                clipboard.set(cloned)
            })

            e.preventDefault()
            return
        }

        // =========================
        // ペースト
        // =========================
        if (e.ctrlKey && e.key.toLowerCase() === 'v') {
            if (!clipboard.value) return

            clipboard.value.clone().then((obj) => {

                canvas.value.discardActiveObject()

                obj.set({
                    left: (obj.left || 0) + 20,
                    top: (obj.top || 0) + 20
                })

                // activeSelection対応
                if (obj.type === 'activeSelection') {

                    obj.canvas = canvas.value

                    obj.forEachObject((o) => {
                        canvas.value.add(o)
                    })

                    const selection = new ActiveSelection(obj.getObjects(), {
                        canvas: canvas.value
                    })

                    canvas.value.setActiveObject(selection)

                } else {
                    canvas.value.add(obj)
                    canvas.value.setActiveObject(obj)
                }

                canvas.value.requestRenderAll()
                saveHistory()

                // deep copy更新
                obj.clone().then((deep) => {
                    clipboard.set(deep)
                })
            })

            e.preventDefault()
            return
        }

        // =========================
        // 削除
        // =========================
        if (e.key === 'Delete' || e.key === 'Backspace') {

            if (!active) return

            if (active.type === 'activeSelection' || active.type === 'group') {
                active.forEachObject((obj) => canvas.value.remove(obj))
            } else {
                canvas.value.remove(active)
            }

            canvas.value.discardActiveObject()
            canvas.value.renderAll()

            saveHistory()
        }
    }

    const bindKeyboard = () => {
        keyHandler = onKeydown
        document.addEventListener('keydown', keyHandler)
    }

    const unbindKeyboard = () => {
        document.removeEventListener('keydown', keyHandler)
        keyHandler = null
    }

    return {
        bindKeyboard,
        unbindKeyboard
    }
}