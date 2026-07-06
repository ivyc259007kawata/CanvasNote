import { ref } from 'vue'

export function useClipboard() {
    const clipboard = ref(null)

    const set = (obj) => {
        clipboard.value = obj
    }

    const get = () => clipboard.value

    const clear = () => {
        clipboard.value = null
    }

    return {
        clipboard,
        set,
        get,
        clear
    }
}