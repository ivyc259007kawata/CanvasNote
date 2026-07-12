import { Rect, IText } from 'fabric'


export function useCanvasEvents(
    canvas,
    state,
    panel,
    saveHistory
) {


    const fabricCanvas = () => {
        return canvas.canvas.value
    }



    const onMouseDown = (event) => {


        const fc = fabricCanvas()

        if (!fc) return



        const tool = state.tool



        // =====================
        // 選択
        // =====================

        if(tool === 'select'){

            return

        }



        // =====================
        // ペン
        // =====================

        if(tool === 'pen'){

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

        if(tool === 'rectangle'){


            const rect = new Rect({

                left:pointer.x,

                top:pointer.y,

                width:120,

                height:120,

                fill:state.color

            })


            fc.add(rect)

            fc.setActiveObject(rect)

            panel.updateActiveObject()


            fc.requestRenderAll()


            saveHistory()


            return

        }




        // =====================
        // テキスト
        // =====================

        if(tool === 'text'){


            const text = new IText(
                'テキスト',
                {

                    left:pointer.x,

                    top:pointer.y,

                    fontSize:30,

                    fill:state.color

                }
            )



            fc.add(text)

            fc.setActiveObject(text)


            panel.updateActiveObject()


            fc.requestRenderAll()


            saveHistory()


            return

        }

    }




    const bindEvents = ()=>{


        const fc = fabricCanvas()

        if(!fc) return


        fc.on(
            'mouse:down',
            onMouseDown
        )

    }




    const unbindEvents = ()=>{


        const fc = fabricCanvas()

        if(!fc) return


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