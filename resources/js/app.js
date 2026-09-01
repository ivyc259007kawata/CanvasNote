import { createApp } from 'vue'
import CanvasApp from './CanvasApp.vue'

const canvasApp = document.getElementById('canvas-app')

if (canvasApp) {
    createApp(CanvasApp).mount(canvasApp)
}