<template>
    <div class="toolbar">

        <button
            @click="$emit('update:tool','select')"
            :class="{active:tool==='select'}">
            🖱 選択
        </button>

        <button
            @click="$emit('update:tool','rectangle')"
            :class="{active:tool==='rectangle'}">
            ▭ 四角
        </button>

        <button
            @click="$emit('update:tool','text')"
            :class="{active:tool==='text'}">
            📝 テキスト
        </button>

        <button
            @click="$emit('update:tool','pen')"
            :class="{active:tool==='pen'}">
            ✏ ペン
        </button>

        <div class="color-picker">

            <button
                class="color"
                style="background:#000000"
                @click="$emit('update:color','#000000')">
            </button>

            <button
                class="color"
                style="background:#ff0000"
                @click="$emit('update:color','#ff0000')">
            </button>

            <button
                class="color"
                style="background:#0066ff"
                @click="$emit('update:color','#0066ff')">
            </button>

            <button
                class="color"
                style="background:#00aa00"
                @click="$emit('update:color','#00aa00')">
            </button>

        </div>

        <button @click="$emit('image')">
            🖼画像
        </button>

        <button @click="$emit('undo')">
            ↩ Undo
        </button>

        <button @click="$emit('redo')">
            ↪ Redo
        </button>

        <button @click="$emit('open')">
            📂 開く
        </button>

        <button @click="$emit('save')">
            💾 保存
        </button>

    </div>
</template>

<script setup>

defineProps({
    tool:String
})

defineEmits([
    'update:tool',
    'update:color',
    'image',
    'undo',
    'redo',
    'open',
    'save'
])

</script>

<style scoped>

.toolbar{
    display:flex;
    gap:10px;
}

.toolbar button{
    padding:8px 16px;
    border:none;
    border-radius:6px;
    background:#3b82f6;
    color:white;
    cursor:pointer;
}

.toolbar button.active{
    background:#1d4ed8;
}

.color-picker{
    display:flex;
    gap:8px;
    align-items:center;
}

.color{
    width:28px;
    height:28px;
    border-radius:50%;
    border:2px solid #ddd;
}

</style>