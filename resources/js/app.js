import './bootstrap'

import Alpine from 'alpinejs'

window.Alpine = Alpine

Alpine.start()

import { createApp } from 'vue'

import CanvasApp from './CanvasApp.vue'
import StudentDashboardView from './views/StudentDashboardView.vue'

const appElement = document.getElementById('app')

if (appElement) {

    const page = appElement.dataset.page

    if (page === 'student') {

        // 生徒Dashboard
        createApp(StudentDashboardView)
            .mount('#app')

    } else {

        // 先生側CanvasNote
        createApp(CanvasApp)
            .mount('#app')
    }
}