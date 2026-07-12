<template>

    <canvas
        ref="canvasElement"
        width="1000"
        height="600"
    ></canvas>

</template>


<script setup>

import { ref,onMounted } from 'vue'


const canvasElement = ref(null)


const props = defineProps({

    canvasEl:Object

})


onMounted(()=>{


    if(props.canvasEl){

        props.canvasEl.value =
            canvasElement.value

    }


})

</script>


<style scoped>

canvas {

    border:1px solid #ccc;

    background:white;

}

</style>