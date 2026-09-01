<template>

    <canvas
        ref="canvasElement"
        width="1000"
        height="600"
    ></canvas>

</template>


<script setup>

import { ref, onMounted } from 'vue'


const canvasElement = ref(null)



const emit = defineEmits([
    'ready'
])



onMounted(()=>{


    emit(
        'ready',
        canvasElement.value
    )


})


</script>



<style scoped>

canvas {
    display: block;

    width: 100%;
    max-width: 1000px;

    height: auto;

    border: 1px solid #d1d5db;
    border-radius: 8px;

    background: white;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.05);
}

</style>