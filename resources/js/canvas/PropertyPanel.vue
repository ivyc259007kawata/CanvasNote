<template>
    <div class="property-panel">

        <h3>プロパティ</h3>

        <template v-if="activeObject">

            <p>タイプ：{{ activeObject.type }}</p>

            <label>色</label>
            <input
                type="color"
                :value="fillColor"
                @input="$emit('update:fillColor', $event.target.value)"
            >

            <label>X</label>
            <input
                type="number"
                :value="left"
                @input="$emit('update:left', Number($event.target.value))"
            >

            <label>Y</label>
            <input
                type="number"
                :value="top"
                @input="$emit('update:top', Number($event.target.value))"
            >

            <label>幅</label>
            <input
                type="number"
                :value="objectWidth"
                @input="$emit('update:objectWidth', Number($event.target.value))"
            >

            <label>高さ</label>
            <input
                type="number"
                :value="objectHeight"
                @input="$emit('update:objectHeight', Number($event.target.value))"
            >

            <label>回転</label>
            <input
                type="number"
                :value="angle"
                @input="$emit('update:angle', Number($event.target.value))"
            >

            <button @click="$emit('front')">
                ⬆ 最前面
            </button>

            <button @click="$emit('back')">
                ⬇ 最背面
            </button>

            <button @click="$emit('delete')">
                🗑 削除
            </button>

        </template>

        <template v-else>
            未選択
        </template>

    </div>
</template>

<script setup>

defineProps({

    activeObject: Object,

    fillColor: String,

    left: Number,

    top: Number,

    objectWidth: Number,

    objectHeight: Number,

    angle: Number

})

defineEmits([

    'update:fillColor',

    'update:left',

    'update:top',

    'update:objectWidth',

    'update:objectHeight',

    'update:angle',

    'front',

    'back',

    'delete'

])

</script>

<style scoped>

.property-panel {
    width: 230px;
    padding: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: white;
}

.property-panel h3 {
    margin: 0 0 8px;
}

.property-panel label {
    font-size: 13px;
    color: #555;
}

.property-panel input[type="number"] {
    padding: 6px;
}

.property-panel input[type="color"] {
    width: 100%;
    height: 36px;
    padding: 2px;
    cursor: pointer;
}

.property-panel button {
    padding: 8px;
    margin-top: 4px;
    cursor: pointer;
}

</style>