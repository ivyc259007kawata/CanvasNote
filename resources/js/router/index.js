import { createRouter, createWebHistory } from 'vue-router'

import DashboardView from '../views/DashboardView.vue'
import CanvasEditorView from '../views/CanvasEditorView.vue'

const routes = [
    {
        path: '/',
        component: DashboardView
    },
    {
        path: '/lesson/:id',
        component: CanvasEditorView
    }
]

export default createRouter({
    history: createWebHistory(),
    routes
})