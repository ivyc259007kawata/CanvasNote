import { ref } from 'vue'


export function useAutoSave(
    lesson,
    pages,
    saveLesson
){

    const timer = ref(null)


    const start = () => {


        if(timer.value){
            return
        }


        timer.value = setInterval(()=>{


            if(!lesson.value){
                return
            }


            pages.saveCurrentPage()

            saveLesson(false)


        },30000)


    }



    const stop = () => {


        if(timer.value){


            clearInterval(timer.value)

            timer.value = null


        }


    }



    return {

        start,

        stop

    }


}