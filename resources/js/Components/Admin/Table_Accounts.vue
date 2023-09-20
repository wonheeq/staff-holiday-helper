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
                label: 'Account ID',
                field: 'accountNo',
                },
                {
                label: 'Account Type',
                field: 'accountType',
                },
                {
                label: 'Surname',
                field: 'lName',
 
                },
                {
                label: 'First/Other Names',
                field: 'fName',
                },
                {
                label: 'School',
                field: 'schoolId',
                },
                {
                label: 'Line Manager',
                field: 'superiorNo',
                },
                {
                label: 'Created/Last Updated',
                field: 'updated_at',
                }
            ],
            accounts: [],
            tHeight: ((0.8889 * window.innerHeight) - 378.2223).toFixed(0) + "px"
        };
    },
    created() {
        //console.warn("/api/allAccounts/" + this.user)
        axios.get("/api/allAccounts/" + this.user)
        .then((response) => {
            this.accounts = response.data;
            //console.log(response.data);
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
                    :rows="accounts"
                    :columns="columns"
                    v-bind:max-height= tHeight
                    :fixed-header="{
                        enabled: true,
                    }"
                    :search-options="{
                        enabled: true,
                        placeholder: 'Search Staff Accounts',
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