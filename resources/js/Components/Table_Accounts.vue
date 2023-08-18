<script setup>

import 'vue-good-table-next/dist/vue-good-table-next.css';
import { VueGoodTable } from 'vue-good-table-next';

</script>

<script>
import axios from "axios";

export default {
    data: function() {
        return {
            columns: [
                {
                label: 'Account Number',
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
                label: 'Other Names',
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
            ],
            accounts: [],
            tHeight: ((0.8889 * window.innerHeight) - 378.2223).toFixed(0) + "px"
        };
    },
    created() {
        axios.get("/api/accounts")
        .then((response) => {
            this.accounts = response.data;
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
            console.warn("tHeight: ", this.tHeight)
        })
    },
    beforeDestroy() { 
        window.removeEventListener('resize', this.onResize); 
    },
    methods: {  
        onResize() {
        this.tHeight = ((0.8889 * window.innerHeight) - 378.2223).toFixed(0) + "px"
        //this.tHeight = (window.innerHeight).toFixed(0) + "px"
        console.warn("tHeight: ", this.tHeight)
        },
    }
};

let onSearch = () => {
};
</script>


<template>

  <div class="justify-center mx-4 mt-4" ref="tableh">
            <div>
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
                        mode: 'pages',
                    }">
                    <template #emptystate>
                        No entries found!
                    </template>        
                </VueGoodTable>
            </div>
       </div>
</template>

<style>
    #filterSection {
        height: 100%;       
    }
</style>