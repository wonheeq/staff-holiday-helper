<script setup>

import 'vue-good-table-next/dist/vue-good-table-next.css';
import { VueGoodTable } from 'vue-good-table-next';

import { useDark } from "@vueuse/core";
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { computed } from 'vue';
const pageDropdown = computed(() => {
    if (isMobile.value) {
        return [10,20,30];
    }
    return [10,20,30,40,50];
});
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const isDark = useDark();
</script>

<script>
import axios from "axios";
import Swal from 'sweetalert2';

export default {
    props: {
        user: {
            type: String,
            required: true
        }
    },
    data: function() {
        let defaultC = 324;
        return {
            columns: [
                {
                label: 'Message ID  ',
                field: 'messageId',
                },
                {
                label: 'Application ID',
                field: 'applicationNo',
                },
                {
                label: 'Reciever ID',
                field: 'receiverNo',
 
                },
                {
                label: 'Sender ID',
                field: 'senderNo',
                },
                {
                label: 'Subject',
                field: 'subject',
                },
                {
                label: 'Content (JSON)',
                field: 'content',
                },
                {
                label: 'Acknowledged?',
                field: 'acknowledged',
                },
                {
                label: 'Created/Last Updated',
                field: 'updated_at',
                },
                {
                label: '',
                field: 'delete',
                sortable: false
                },
            ],
            Messages: [],
            c: defaultC,
            tHeight: ((0.8889 * window.innerHeight) - defaultC).toFixed(0) + "px"};
    },
    created() {
        axios.get("/api/allMessages/" + this.user)
        .then((response) => {
            this.Messages = response.data;
            //console.log(response.data);
        })
        .catch((error) => {
            console.log(error);
        });
        if (screen.width >= 3840) {          
            this.c = 468;
            this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        }
    },
    // Using height of window to determine max table height
    mounted() {
        this.$nextTick(() => {
            window.addEventListener('resize', this.onResize);
            //console.warn("tHeight: ", this.tHeight)
        })
    },
    beforeDestroy() { 
        window.removeEventListener('resize', this.onResize); 
    },
    methods: {  
        onResize() {
            this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        //this.tHeight = (window.innerHeight).toFixed(0) + "px"
        ////console.warn("tHeight: ", this.tHeight)
        },
        deleteClicked: function(rowId) {
            //console.log(rowId);
            Swal.fire({
                icon: 'warning',
                title: 'Delete \'' + rowId + '\'?',
                text: 'This will remove the message from the database.',
                showDenyButton: true,
                confirmButtonText: 'Yes',
                confirmButtonColor: '#22C55E',
            })
            .then((result) => {
                if (result.isConfirmed) {
                    this.deleteEntry(rowId);
                }
            });
        },
        deleteEntry: function(rowId) {
            //console.log('deleting');

            let data = {
                'table': 'messages',
                'entryId': rowId
            }

            // Removing Messages from DB
            axios.post("/api/dropEntry/" + this.user, data)
            .then((response) => {
                if (response.status == 200) {   
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully deleted message.'
                    });

                    // Reset Table
                    axios.get("/api/allMessages/" + this.user)
                    .then((response) => {
                        this.Messages = response.data;
                        //console.log(response.data);
                    })
                    .catch((error) => {
                        console.log(error);
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
    }
};
</script>


<template>
    <div class="parent1">
        <div class="laptop:mx-4 laptop:mt-4 4k:mt-8">
            <div remove-tailwind-bg>
                <VueGoodTable 
                    :theme="isDark?'nocturnal':''"
                    :rows="Messages"
                    :columns="columns"
                    v-bind:max-height= tHeight
                    :fixed-header="!isMobile"
                    :search-options="{
                        enabled: true,
                        placeholder: 'Search Messages',
                    }"
                    :pagination-options="{
                        enabled: true,
                        //mode: 'pages',
                        perPage: 30,
                        perPageDropdown: pageDropdown
                    }">
                    <template #table-row="props">
                        <span v-if="props.column.field == 'delete'">
                            <button type="button" class="4k:w-10 4k:h-10" v-on:click="deleteClicked(props.row.messageId)">
                                <img src="/images/delete.svg" :class="isDark?'darkModeImage':''"/>
                            </button>
                        </span>
                    </template>
                    <template #emptystate>
                        No entries found!
                    </template>        
                </VueGoodTable> 
            </div>           
       </div>
    </div>
</template>

<style>
    .parent1 {
        width: 100%;       
    }
</style>

