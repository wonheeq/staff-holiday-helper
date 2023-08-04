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
                field: 'aType',
                },
                {
                label: 'Surname',
                field: 'lName',
 
                },
                {
                label: 'Other Names',
                field: 'fNames',
                },
                {
                label: 'Line Manager',
                field: 'superiorNo',
                },
            ],
            accounts: [],
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
};

let onSearch = () => {
};
</script>


<template>
<!--    mt = 'margin top' - How much empty space is above an element
        mx = horizontal margin - How much empty space to either side of an elements-->

  <div class="justify-center screen mx-4 mt-4">
            <div>
               <VueGoodTable 
                   :rows="accounts"
                   :columns="columns"
                   max-height="300px" 
                   :fixed-header="{
                   enabled: true,
                   }"
                   :search-options="{
                   enabled: true,
                   placeholder: 'Search Staff Accounts',
                   }"
                   :pagination-options="{
                   enabled: true,
                   }">
                    

               </VueGoodTable>
            </div>
       </div>
</template>

<style>
    
</style>