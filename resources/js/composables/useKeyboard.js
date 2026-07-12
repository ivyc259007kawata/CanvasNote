import { ActiveSelection } from 'fabric'


export function useKeyboard(
    canvasManager,
    saveHistory,
    clipboard
) {

    let keyHandler = null

    const fabricCanvas = () => {
        return canvasManager.canvas.value
    }

    const onKeydown = (e) => {

        const canvas = fabricCanvas()

        if (!canvas) return

        const active =
            canvas.getActiveObject()
        // =========================
        // コピー
        // =========================
        if (
            e.ctrlKey &&
            e.key.toLowerCase() === 'c'
        ) {
            if (!active) return
            active.clone()
                .then((cloned) => {
                    clipboard.set(cloned)
                })
            e.preventDefault()
            return
        }

        // =========================
        // ペースト
        // =========================
        if (
            e.ctrlKey &&
            e.key.toLowerCase() === 'v'
        ) {

            const copied =
                clipboard.get()
            if (!copied) return
            copied.clone()
                .then((obj) => {
                    canvas.discardActiveObject()
                    obj.set({
                        left:
                            (obj.left || 0) + 20,
                        top:
                            (obj.top || 0) + 20
                    })

                    // 複数選択コピー
                    if (
                        obj.type === 'activeSelection'
                    ) {
                        obj.canvas = canvas
                        obj.forEachObject(
                            (o) => {
                                canvas.add(o)
                            }
                        )

                        const selection =
                            new ActiveSelection(
                                obj.getObjects(),
                                {
                                    canvas
                                }
                            )
                        canvas.setActiveObject(
                            selection
                        )
                    } else {
                        canvas.add(obj)
                        canvas.setActiveObject(
                            obj
                        )
                    }
                    canvas.requestRenderAll()
                    saveHistory()
                    // コピー元更新
                    obj.clone()
                        .then((deep) => {
                            clipboard.set(deep)
                        })

                })
            e.preventDefault()
            return
        }
        // =========================
        // 削除
        // =========================
        if (
            e.key === 'Delete' ||
            e.key === 'Backspace'
        ) {
            if (!active) return
            if (
                active.type === 'activeSelection' ||
                active.type === 'group'
            ) {
                active.forEachObject(
                    (obj) => {
                        canvas.remove(obj)
                    }
                )

            } else {

                canvas.remove(active)
            }
            canvas.discardActiveObject()
            canvas.requestRenderAll()
            saveHistory()
        }
    }

    const bindKeyboard = () => {
        keyHandler = onKeydown
        document.addEventListener(
            'keydown',
            keyHandler
        )
    }

    const unbindKeyboard = () => {
        if (!keyHandler) return
        document.removeEventListener(
            'keydown',
            keyHandler
        )
        keyHandler = null
    }

    return {
        bindKeyboard,
        unbindKeyboard

    }


}