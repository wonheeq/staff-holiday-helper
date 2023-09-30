<script setup>

import 'vue-good-table-next/dist/vue-good-table-next.css';
import { VueGoodTable } from 'vue-good-table-next';

</script>

<script>
import axios from "axios";

export default {
    props: {
        user: {
            type: String,
            required: true
        }
    },
    data: function() {
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
                label: 'Content',
                field: 'content',
                },
                {
                label: 'Acknowledged?',
                field: 'acknowledged',
                },
                {
                label: 'Created/Last Updated (UTC)',
                field: 'updated_at',
                }
            ],
            Messages: [],
            tHeight: ((0.8889 * window.innerHeight) - 378.2223).toFixed(0) + "px"
        };
    },
    created() {
        axios.get("/api/allMessages/" + this.user)
        .then((response) => {
            this.Messages = response.data;
            console.log(response.data);
        })
        .catch((error) => {
            console.log(error);
        });
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
        this.tHeight = ((0.8889 * window.innerHeight) - 378.2223).toFixed(0) + "px"
        //this.tHeight = (window.innerHeight).toFixed(0) + "px"
        //console.warn("tHeight: ", this.tHeight)
        },
    }
};

let onSearch = () => {
};
</script>


<template>
    <div class="parent1">
        <div class="mx-4 mt-4">
            <div remove-tailwind-bg>
                <VueGoodTable 
                    :rows="Messages"
                    :columns="columns"
                    v-bind:max-height= tHeight
                    :fixed-header="{
                        enabled: true,
                    }"
                    :search-options="{
                        enabled: true,
                        placeholder: 'Search Messages',
                    }"
                    :pagination-options="{
                        enabled: true,
                        //mode: 'pages',
                        perPage: 30
                    }">
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

