import { ref } from 'vue'

export function useSaveLoad(canvas) {

    const FILE_NAME = 'CanvasNote'

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
        const blob = new Blob([json], { type: 'application/json' })

        downloadBlob(blob, `${FILE_NAME}.canvas`)
    }

    const saveAsImage = (format) => {
        if (!canvas.value) return

        const dataUrl = canvas.value.toDataURL({
            format,
            quality: 1
        })

        fetch(dataUrl)
            .then(res => res.blob())
            .then(blob => {
                const ext = format === 'jpeg' ? 'jpg' : 'png'
                downloadBlob(blob, `${FILE_NAME}.${ext}`)
            })
    }

    return {
        saveAsCanvas,
        saveAsImage
    }
}