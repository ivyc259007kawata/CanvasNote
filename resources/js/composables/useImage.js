import { FabricImage } from 'fabric'

export function useImage(canvas, saveHistory, updateActiveObject) {

    const loadImageFromFile = (file) => {

        const reader = new FileReader()

        reader.onload = async () => {

            const img = await FabricImage.fromURL(reader.result)

            img.set({
                left: 100,
                top: 100
            })

            const maxWidth = 400

            if (img.width > maxWidth) {
                img.scale(maxWidth / img.width)
            }

            canvas.value.add(img)
            canvas.value.setActiveObject(img)

            updateActiveObject?.()
            canvas.value.requestRenderAll()

            saveHistory()
        }

        reader.readAsDataURL(file)
    }

    return {
        loadImageFromFile
    }
}