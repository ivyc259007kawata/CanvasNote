<template>
    <div
        v-if="show"
        class="dialog-overlay"
        @click.self="$emit('close')"
    >
        <div class="dialog-box">

            <h3>保存形式を選択</h3>

            <button @click="$emit('canvas')">
                .canvas
            </button>

            <button @click="$emit('png')">
                .png
            </button>

            <button @click="$emit('jpg')">
                .jpg
            </button>

            <button @click="$emit('close')">
                キャンセル
            </button>

        </div>
    </div>
</template>

<script setup>

defineProps({
    show: Boolean
})

defineEmits([
    'canvas',
    'png',
    'jpg',
    'close'
])

</script>

<style scoped>

.dialog-overlay {
    position: fixed;
    inset: 0;

    background: rgba(0,0,0,.4);

    display:flex;
    justify-content:center;
    align-items:center;

    z-index:1000;
}

.dialog-box{
    width:320px;

    padding:24px;

    background:white;

    border-radius:12px;

    display:flex;
    flex-direction:column;
    gap:10px;
}

.dialog-box button{

    padding:10px;

}

</style>