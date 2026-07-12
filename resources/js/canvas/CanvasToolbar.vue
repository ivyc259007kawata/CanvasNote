<template>
    <div class="toolbar">

        <button
            @click="$emit('update:tool', 'select')"
            :class="{ active: tool === 'select' }">
            🖱 選択
        </button>

        <button
            @click="$emit('update:tool', 'rectangle')"
            :class="{ active: tool === 'rectangle' }">
            ▭ 四角
        </button>

        <button
            @click="$emit('update:tool', 'text')"
            :class="{ active: tool === 'text' }">
            📝 テキスト
        </button>

        <button
            @click="$emit('update:tool', 'pen')"
            :class="{ active: tool === 'pen' }">
            ✏ ペン
        </button>

        <div class="divider"></div>

        <input
            type="color"
            :value="color"
            @input="$emit('update:color', $event.target.value)"
        />

        <div class="divider"></div>

        <button @click="$emit('image')">🖼 画像</button>

        <button @click="$emit('undo')">↩ Undo</button>

        <button @click="$emit('redo')">↪ Redo</button>

        <button @click="$emit('open')">📂 開く</button>

        <button @click="$emit('save')">💾 保存</button>

    </div>
</template>

<script setup>

defineProps({
    tool: String,
    color: String
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
    flex-wrap:wrap;
    gap:10px;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
    background:#f8f8f8;
}

button{
    padding:8px 14px;
    border:none;
    border-radius:6px;
    background:#3b82f6;
    color:white;
    cursor:pointer;
}

button:hover{
    background:#2563eb;
}

button.active{
    background:#1d4ed8;
}

input[type="color"]{
    width:42px;
    height:42px;
    padding:0;
    border:none;
    background:none;
    cursor:pointer;
}

.divider{
    width:1px;
    background:#ccc;
    margin:0 4px;
}

</style>