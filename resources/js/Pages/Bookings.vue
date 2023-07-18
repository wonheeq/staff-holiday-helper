<script setup>
import LoggedInView from "@/Components/LoggedInView.vue";
import 'vue-good-table-next/dist/vue-good-table-next.css';
import { VueGoodTable } from 'vue-good-table-next';
let list = [
    {
        id: "1",
        name: "jeff",
        num: 123,
    },
    {
        id: "2",
        name: "asdas",
        num: 3,
    },
    {
        id: "3",
        name: "jefsdsdf",
        num: 54,
    },
    {
        id: "4",
        name: "ddd",
        num: 122343,
    },
    {
        id: "5",
        name: "dasd",
        num: 44,
    },
];

let columns = [
    {
        label: 'ID',
        field: 'id',
    },
    {
        label: 'Name',
        field: 'name',
    },
    {
        label: 'Number',
        field: 'num',
        type: 'number',
    },
];

let onSearch = () => {

};
</script>

<template>
    <LoggedInView>
        <div class="flex screen mx-4 mt-4">
           Bookings page

           <div>
                <VueGoodTable
                    :rows="list"
                    :columns="columns"
                    :search-options="{
                    enabled: true,
                }">

                </VueGoodTable>
           </div>
        </div>
    </LoggedInView>
</template>

<style>
</style>