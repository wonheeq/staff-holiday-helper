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
            parentHeight: 0,
            tHeight: "300px"
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
    mounted() {
        const parent = this.$refs.tableh
        this.parentHeight = parent.offsetHeight
        this.tHeight = this.parentHeight + "px"
        console.warn("parentHeight: ", this.parentHeight)
        console.warn("tHeight: ", this.tHeight)
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