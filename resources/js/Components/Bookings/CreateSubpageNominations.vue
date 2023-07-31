<script setup>
import { reactive } from "vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import Nomination from "./Nomination.vue";
let deadAreaColor = "#FFFFFF";

let roles = reactive([
    {
        selected: false,
        role: 'COMP2007: Something something',
        nomination: null,
        disabled: false,
    },
    {
        selected: false,
        role: 'COMP3007: Something something',
        nomination: null,
        disabled: false,
    },
    {
        selected: false,
        role: 'COMP3001: Something something',
        nomination: null,
        disabled: false,
    },
    {
        selected: false,
        role: 'ISEC2001: Something something',
        nomination: null,
        disabled: false,
    },
    {
        selected: false,
        role: 'ISEC2007: Something something',
        nomination: null,
        disabled: false,
    },
    {
        selected: false,
        role: 'ISAD3001: Something something',
        nomination: null,
        disabled: false,
    },
]);
</script>
<template>
    <div class="flex flex-col w-full h-full">
        <div class="flex flex-col w-full h-[10%]">
            <p class="text-4xl">
                Nominate Substitutes:
            </p>
            <div class="flex flex-row justify-between">
                <div class="flex flex-row space-x-4 w-3/5">
                    <div class="flex flex-col items-center pb-2">
                        <p class="text-xl">
                            Select
                        </p>
                        <input type="checkbox" class="w-8 h-8" />
                    </div>
                    <div class="w-full">
                        <p class="text-xl">
                            Filter Roles
                        </p>
                        <input type="text" class="h-8 w-2/3" />
                    </div>
                </div>
                <div class="flex flex-col w-2/5 ">
                    <p class="text-xl">
                        Staff Member
                    </p>
                    <input type="text" class="h-8 w-full"/>
                </div>
            </div>
        </div>
        <div class="h-5/6 mt-2 h-[90%]">
            <div class="border border-black h-[90%]">
                <VueScrollingTable
                    class="scrollTable"
                    :deadAreaColor="deadAreaColor"
                    :scrollHorizontal="false"
                >
                    <template #tbody>
                        <Nomination v-for="role in roles" :role="role"/>
                    </template>
                </VueScrollingTable>
            </div>
            <div class="flex flex-col h-[10%] mb-4">
                <div class="flex items-center space-x-2 py-2 h-2/4">
                    <input type="checkbox"/>
                    <p>This period of leave will not affect my ability to handle all my responsibilities and as such, no nominations are required.</p>
                </div>
                <div class="flex justify-between h-2/4 space-x-16">
                    <button class="bg-red-500 rounded-md text-white font-bold text-2xl w-1/2">
                        Cancel Application
                    </button>
                    <button class="bg-green-500 rounded-md text-white font-bold text-2xl w-1/2">
                        Submit Application
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
.scrollTable{
    overflow-y: auto;
}
</style>