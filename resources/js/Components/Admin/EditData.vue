<script setup>
    import { reactive } from 'vue';
    import Modal from '../Modal.vue';
    import Swal from 'sweetalert2';

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

    const subpageClass = "rounded-bl-md rounded-br-md laptop:rounded-tr-md bg-white";
</script>

<template>
    <Modal>
        <div class="h-screen flex items-center w-screen bg-transparent">
            <div class="w-3/6 flex flex-col p-4 mx-auto h-4/7 rounded-tl-md overflow-auto" :class="subpageClass">
                <div class="h-[10%] flex justify-between 4k:ml-6">
                    <slot />
                    <p class="text-xl mt-1 font-bold 4k:text-3xl 4k:mt-6">             
                        Edit {{ table }}:
                    </p>

                    <!--Add full ui then worry about implementation-->
                    <button class="h-full" @click="$emit('close')">
                        <img src="/images/close.svg" class="h-full w-full"/>
                    </button>
                </div>
                <p class="4k:text-2xl">             
                    Re-fill the values you wish to change.
                </p>
                <p  v-if="table == 'Staff Accounts'" class="4k:text-2xl text-red-700">             
                    Note: Changing your own account details will require you to log back in.
                </p>
                <div class="mt-3 4k:ml-6">
                    <div v-for="(, index) in entry" :key="index">
                        <div class="flex justify-between mx-4 4k:mx-6">
                            <span class="mt-4 4k:mt-10 4k:text-2xl">{{ index }}: </span>
                            <input class="input_options" 
                                type="text" autocomplete="off" 
                                v-model="entry[index]"
                            />
                        </div>
                    </div>
                </div>
                <div class="flex mt-5 justify-center">
                    <button class="bg-[#e3e3e3] px-6 py-2 ml-4 text-center w-28 text-lg 4k:text-3xl"
                        @click="updateTuple()">
                        Update 
                    </button>
                </div>
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