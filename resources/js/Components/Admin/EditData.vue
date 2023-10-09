<script setup>
    import { reactive } from 'vue';
    import Modal from '../Modal.vue';
    import Swal from 'sweetalert2';
    import { useDark } from "@vueuse/core";
    import { storeToRefs } from 'pinia';
    import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
    const screenSizeStore = useScreenSizeStore();
    const { isMobile } = storeToRefs(screenSizeStore);
    const isDark = useDark();
    let emit = defineEmits(['close']);

    let props = defineProps({
        table: String,
        entry: Object,
        user: String,
        required: true,
    });

    const initialEntry = Object.assign({}, props.entry);

    /*let msg = reactive({
        warning: false,
        errorMsg: "default"
    });*/

    function updateTuple() {
        let data = {
            'table': props.table,
            'entry': props.entry,
            'initialEntry': initialEntry
        }

        axios.post("/api/editEntry/" + props.user, data)
            .then((response) => {
                if (response.status == 200) {   
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully edited entry.'
                    });            
                }
            })
            .catch((error) => {
                console.log(error);

                Swal.fire({
                    icon: "error",
                    title: 'Error',
                    text: error.response.data.error
                });
            });
    } 
</script>

<template>
    <Modal>
        <div class="w-3/6 4k:w-[70rem] flex flex-col p-4 mx-auto h-4/7 4k:h-[48rem] rounded-tl-md overflow-auto rounded-bl-md rounded-br-md laptop:rounded-tr-md"
            :class="isDark?'bg-gray-800':'bg-white'" 
        >
            <div class="h-[10%] flex justify-between 4k:ml-6">
                <slot />
                <p class="text-xl mt-1 font-bold 4k:text-3xl 4k:mt-6">             
                    Edit {{ table }}:
                </p>

                <!--Add full ui then worry about implementation-->
                <button class="h-full" @click="$emit('close')">
                    <img src="/images/close.svg" class="h-full w-full" :class="isDark?'darkModeImage':''"/>
                </button>
            </div>
            <p class="4k:text-2xl 4k:ml-6 4k:mt-3">             
                Re-fill the values you wish to change.
            </p>
            <p  v-if="table == 'Staff Accounts'" class="4k:text-2xl text-red-700 4k:ml-6">             
                Note: Changing your own account details will require you to log back in.
            </p>
            <div class="mt-3 ">
                <div v-for="(, index) in entry" :key="index">
                    <div class="flex justify-between mx-4 4k:mx-6">
                        <span class="mt-4 4k:mt-10 4k:text-2xl">{{ index }}: </span>
                        <input class="input_options" 
                            :class="isDark?'bg-gray-900':''"
                            type="text" autocomplete="off" 
                            v-model="entry[index]"
                        />
                    </div>
                </div>
            </div>
            <div class="flex mt-5 justify-center">
                <button class="px-6 py-2 ml-4 text-center w-28 text-lg 4k:text-3xl 4k:mt-2 4k:w-36"
                    :class="{
                        'bg-[#e3e3e3]': !isDark,
                        'bg-gray-600': isDark
                    }"
                    @click="updateTuple()">
                    Update 
                </button>
            </div>
        </div>
    </Modal>
</template>


<style>
</style>

<style lang="postcss">

    .input_options {
        width: 30rem; 
        height: 2rem; 
        margin-top: 0.75rem;
        @apply 4k:text-2xl 4k:h-11 4k:w-[50rem] 4k:mt-9 !important;
    }
</style>