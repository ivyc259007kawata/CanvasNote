<template>
    <div class="page-tabs">

        <div
            v-for="(page, index) in pages"
            :key="page.id"
            class="page-item"
            draggable="true"

            @dragstart="dragStart(index)"
            @dragover.prevent
            @drop="drop(index)"
        >

            <button
                class="page-button"
                :class="{ active: currentPage === index }"

                @click="emit('change', index)"
                @dblclick="rename(index)"
            >

                <div class="thumbnail">

                    <img
                        v-if="page.thumbnail"
                        :src="page.thumbnail"
                    >

                    <div
                        v-else
                        class="empty-thumbnail"
                    >
                        No Image
                    </div>

                </div>


                <div class="title">

                    {{ page.title }}

                </div>


            </button>


            <button
                class="delete-button"
                @click="emit('delete', index)"
            >

                ✕

            </button>


        </div>


        <button
            class="add-button"
            @click="emit('add')"
        >

            ＋ ページ追加

        </button>


    </div>
</template>



<script setup>

import { ref } from 'vue'


const props = defineProps({

    pages:Array,

    currentPage:Number

})


const emit = defineEmits([

    'change',

    'add',

    'delete',

    'rename',

    'move'

])



const dragIndex = ref(null)



// ドラッグ開始
const dragStart = (index)=>{

    dragIndex.value = index

}



// ドロップ
const drop = (index)=>{


    if(dragIndex.value === null)
        return


    if(dragIndex.value === index)
        return



    emit(
        'move',
        {

            oldIndex:dragIndex.value,

            newIndex:index

        }
    )


    dragIndex.value = null


}



// 名前変更
const rename = (index)=>{


    const title = prompt(

        'ページ名を入力してください',

        props.pages[index].title

    )


    if(title === null)
        return



    emit(

        'rename',

        {

            index,

            title

        }

    )

}


</script>



<style scoped>


.page-tabs{

    display:flex;

    flex-wrap:wrap;

    gap:10px;

    margin-bottom:16px;

}



.page-item{

    display:flex;

    align-items:center;

    gap:4px;

}



.page-button{

    width:180px;

    display:flex;

    flex-direction:column;

    align-items:center;

    gap:8px;

    padding:10px;

    border:1px solid #ccc;

    border-radius:8px;

    background:white;

    cursor:pointer;

}



.page-button:hover{

    background:#f3f4f6;

}



.page-button.active{

    background:#2563eb;

    color:white;

    border-color:#2563eb;

}



.thumbnail{

    width:150px;

    height:90px;

    border:1px solid #ccc;

    overflow:hidden;

    background:white;

}



.thumbnail img{

    width:100%;

    height:100%;

    object-fit:cover;

}



.empty-thumbnail{

    width:100%;

    height:100%;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#999;

    font-size:12px;

}



.title{

    font-size:14px;

    text-align:center;

}



.delete-button{

    width:24px;

    height:24px;

    border:none;

    border-radius:50%;

    background:#ef4444;

    color:white;

    cursor:pointer;

}



.delete-button:hover{

    background:#dc2626;

}



.add-button{

    padding:8px 14px;

    border:1px dashed #999;

    border-radius:6px;

    background:white;

    cursor:pointer;

}



.add-button:hover{

    background:#f3f4f6;

}


</style>