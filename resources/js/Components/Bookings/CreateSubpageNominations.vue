<script setup>
import axios from "axios"; 
import { reactive, ref, computed, onMounted } from "vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import Nomination from "./Nomination.vue";
import NomineeDropdown from "@/Components/Bookings/NomineeDropdown.vue";
import { useNominationStore } from '@/stores/NominationStore';
import { storeToRefs } from 'pinia';
let nominationStore = useNominationStore();
const { nominations } = storeToRefs(nominationStore);

let deadAreaColor = "#FFFFFF";

let allSelected = ref(false);
let roleFilter = ref("");
let staffMembers = reactive([]);


let fetchStaffMembers = async() => {
    try {
        const resp = await axios.get('/api/getBookingOptions/a000000');
        staffMembers = resp.data;
    } catch (error) {
        alert("Failed to load data: Please try again");
        console.log(error);
    }
}; 

const dataReady = ref(false);

onMounted(async () => {
    await fetchStaffMembers();
    dataReady.value = true;
});

function handleDropdownStaffSelection(selection) {
    for (let nomination of nominations.value) {
        if (nomination.selected) {
            nomination.nomination = selection;
        }
    }
}

function handleSelectAll() {
    if (allSelected.value == true) {
        for (let nomination of nominations.value) {
            if (nomination.visible) {
                nomination.selected = true;
            }
            else {
                nomination.selected = false;
            }
        }
    }
    else {
        for (let nomination of nominations.value) {
            if (nomination.visible) {
                nomination.selected = false;
            }
            else {
                nomination.selected = true;
            }
        }
    }
}

const numSelectedNominations = computed(() => {
    return nominations.value.filter(nomination => nomination.selected).length;
});

const filteredNominations = computed(() => {
    let filtered = nominations.value.filter(nomination => nomination.role.toLowerCase().includes(roleFilter.value.toLowerCase()));

    // get true index of filtered items
    let filteredTrueIndices = [];
    for (let nom of filtered) {
        filteredTrueIndices.push(nominations.value.indexOf(nom));
    }

    // iterate through nominations and set visible to false for those not in filteredTrueIndices
    for (let i = 0; i < nominations.value.length; i++) {
        if (!filteredTrueIndices.includes(i)) {
            nominations.value[i].visible = false;
        }
        else {
            nominations.value[i].visible = true;
        }
    }

    return filtered;
});
</script>
<template>
    <div class="flex flex-col w-full pageHeight" v-if="dataReady">
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
                        <input type="checkbox"
                            class="w-8 h-8"
                            v-model="allSelected"
                            @change="handleSelectAll()"    
                        />
                    </div>
                    <div class="w-full">
                        <p class="text-xl">
                            Filter Roles
                        </p>
                        <input type="text"
                            class="h-8 w-2/3"
                            v-model="roleFilter"    
                        />
                    </div>
                </div>
                <div class="flex flex-col w-96 mr-6">
                    <p class="text-xl">
                        Select Staff Member for {{ numSelectedNominations }} Entries
                    </p>
                    <NomineeDropdown
                        class="w-full"
                        :options="staffMembers"
                        @optionSelected="(selection) => handleDropdownStaffSelection(selection)"    
                    />
                </div>
            </div>
        </div>
        <div class="flex flex-col h-[90%] mt-2">
            <div class="flex border border-black tableHeight">
                <VueScrollingTable
                    class="scrollTable"
                    :deadAreaColor="deadAreaColor"
                    :scrollHorizontal="false"
                >
                    <template #tbody>
                        <div>
                            <Nomination
                            v-for="nomination in filteredNominations"
                            :nomination="nomination"
                            :options="staffMembers"
                        />
                        </div>
                    </template>
                </VueScrollingTable>
            </div>
            <div class="h-[10%]">
                <div class="flex items-center space-x-2 py-2 h-1/2">
                    <input type="checkbox"/>
                    <p>This period of leave will not affect my ability to handle all my responsibilities and as such, no nominations are required.</p>
                </div>
                <div class="flex justify-between h-1/2 space-x-16">
                    <button class="py-2 bg-red-500 rounded-md text-white font-bold text-2xl w-1/2">
                        Cancel Application
                    </button>
                    <button class="py-2 bg-green-500 rounded-md text-white font-bold text-2xl w-1/2">
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
.pageHeight{
    height: calc(0.92 * 0.95 * (93vh - 3rem) - 1rem);
}
.tableHeight{
    height: calc(0.9 * 0.9 * 0.92 * 0.95 * (93vh - 3rem) - 2rem);
}
</style>