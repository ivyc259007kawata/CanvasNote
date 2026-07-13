import { Rect, IText } from 'fabric'
import { watch } from 'vue'


export function useCanvasEvents(
    canvas,
    state,
    panel,
    saveHistory
) {


    const fabricCanvas = () => {
        return canvas.value
    }



    const onMouseDown = (event) => {


        const fc = fabricCanvas()

        if (!fc) return



        const tool = state.tool



        // =====================
        // 選択
        // =====================

        if (tool === 'select') {

            return

        }



        // =====================
        // ペン
        // =====================

        if (tool === 'pen') {

            fc.isDrawingMode = true

            return

        }



        // ペン解除

        fc.isDrawingMode = false



        const pointer =
            fc.getScenePoint(event.e)




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


            // ★ 追加後は選択ツールに自動で戻す
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


            // ★ 追加後は選択ツールに戻し、そのまま文字入力できるようにする
            state.tool = 'select'

            text.enterEditing()

            text.selectAll()


            return

        }

    }




    const bindEvents = () => {


        const fc = fabricCanvas()

        if (!fc) return


        fc.on(
            'mouse:down',
            onMouseDown
        )


        // ★ ツール切り替え時、ペンモードのON/OFFを確実に同期させる
        watch(
            () => state.tool,
            (tool) => {

                const current = fabricCanvas()

                if (!current) return

                current.isDrawingMode = tool === 'pen'

            }
        )

    }




    const unbindEvents = () => {


        const fc = fabricCanvas()

        if (!fc) return


        fc.off(
            'mouse:down',
            onMouseDown
        )

    }



    return {

        bindEvents,

        unbindEvents

    }

}