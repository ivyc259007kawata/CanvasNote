import { ref } from 'vue'

export function useSaveLoad(canvas) {

    const showSaveDialog = ref(false)

    const FILE_NAME = 'CanvasNote'

    const openSaveDialog = () => {
        showSaveDialog.value = true
    }

    const closeSaveDialog = () => {
        showSaveDialog.value = false
    }

    const downloadBlob = (blob, filename) => {

        const url = URL.createObjectURL(blob)

        const a = document.createElement('a')
        a.href = url
        a.download = filename
        a.click()

        URL.revokeObjectURL(url)
    }

    const saveAsCanvas = () => {

        if (!canvas.value) return

        const json = JSON.stringify(canvas.value.toJSON())

        const blob = new Blob([json], {
            type: 'application/json'
        })

        downloadBlob(blob, `${FILE_NAME}.canvas`)

        closeSaveDialog()
    }

    const saveAsImage = (format) => {

        if (!canvas.value) return

        const dataUrl = canvas.value.toDataURL({
            format,
            quality: 1
        })

        fetch(dataUrl)
            .then(r => r.blob())
            .then(blob => {

                const ext =
                    format === 'jpeg'
                        ? 'jpg'
                        : 'png'

                downloadBlob(
                    blob,
                    `${FILE_NAME}.${ext}`
                )

            })

        closeSaveDialog()
    }

    const loadCanvasFile = async (event) => {

        const file = event.target.files[0]

        event.target.value = ''

        if (!file) return

        const text = await file.text()

        const json = JSON.parse(text)

        await canvas.value.loadFromJSON(json)

        canvas.value.requestRenderAll()
    }

    return {
        showSaveDialog,
        openSaveDialog,
        closeSaveDialog,
        saveAsCanvas,
        saveAsImage,
        loadCanvasFile
    }

}