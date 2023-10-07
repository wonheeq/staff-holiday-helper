<script setup>
    import { reactive } from 'vue';
    import Modal from '../Modal.vue';
    import Swal from 'sweetalert2';

    let emit = defineEmits(['close']);

    let props = defineProps({
        table: String,
        entryTable: String,
        user: String,
        required: true
    });

    /*let msg = reactive({
        warning: false,
        errorMsg: "default"
    });*/


    const subpageClass = "rounded-bl-md rounded-br-md laptop:rounded-tr-md bg-white";
</script>

<template>
    <Modal>
        <div class="h-screen flex items-center w-screen bg-transparent">
            <div class="w-3/6 flex flex-col p-4 mx-auto h-4/6 rounded-tl-md overflow-auto" :class="subpageClass">
                <div class="h-[10%] flex justify-between 4k:ml-6">
                    <slot />
                    <p class="text-xl font-bold 4k:text-3xl 4k:mt-6">             
                        Edit - {{ table }} - {{ user }}
                    </p>
                    <!--Add full ui then worry about implementation-->
                    <button class="h-full" @click="$emit('close')">
                        <img src="/images/close.svg" class="h-2/3 w-2/3"/>
                    </button>
                </div>
            </div>
        </div>
    </Modal>
</template>


<style>


</style>