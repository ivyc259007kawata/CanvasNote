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

    border:1px solid #ccc;

    background:white;

}

</style>