<script setup>
import axios from "axios"; 
import { reactive, ref, computed, onMounted } from "vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import Swal from 'sweetalert2'
import Nomination from "./Nomination.vue";
import NomineeDropdown from "@/Components/Bookings/NomineeDropdown.vue";
import { storeToRefs } from 'pinia';
import { useUserStore } from "@/stores/UserStore";
import { useNominationStore } from '@/stores/NominationStore';
let userStore = useUserStore();
const { userId } = storeToRefs(userStore);
let nominationStore = useNominationStore();
const { nominations } = storeToRefs(nominationStore);
const { fetchNominations } = nominationStore;

let emit = defineEmits(['resetFields', 'submitApplication']);

let deadAreaColor = "#FFFFFF";

let selfNominateAll = ref(false);
let allSelected = ref(false);
let roleFilter = ref("");
let staffMembers = reactive([]);


let fetchStaffMembers = async() => {
    try {
        const resp = await axios.get('/api/getBookingOptions/000000a');
        staffMembers = resp.data;
    } catch (error) {
        alert("Failed to load data: Please try again");
        console.log(error);
    }
}; 

const dataReady = ref(false);

onMounted(async () => {
    await fetchNominations();
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

function handleSelfNominateAll() {
    if (selfNominateAll) {
        for (let nomination of nominations.value) {
            nomination.nomination = "Self Nomination";
            nomination.selected = false;
            nomination.visible = true;
        }
        allSelected = false;
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

function resetFields() {
    for (let nomination of nominations.value) {
        nomination.nomination = "";
        nomination.selected = false;
        nomination.visible = true;
    }
    allSelected = false;
    selfNominateAll = false;
    emit('resetFields');
}

function cancelApplication() {
    Swal.fire({
        icon: 'warning',
        title: 'Cancel Application?',
        text: 'This will reset all fields on this page.',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        confirmButtonColor: '#22C55E',
    })
    .then((result) => {
        if (result.isConfirmed) {
            resetFields();
        }
    });
}

function submitApplication() {
    let data = {
        'accountNo': userId,
        'selfNominateAll': selfNominateAll,
    }
    // pass data to parent to handle
    emit('submitApplication', data);
}

const disabledClass = "bg-gray-300 border-gray-100";
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
                            :class="selfNominateAll ? disabledClass : ''"
                            v-model="allSelected"
                            @change="handleSelectAll()"    
                            :disabled="selfNominateAll"
                        />
                    </div>
                    <div class="w-full">
                        <p class="text-xl">
                            Filter Roles
                        </p>
                        <input type="text"
                            class="h-8 w-2/3"
                            :class="selfNominateAll ? disabledClass : ''"
                            v-model="roleFilter"
                            :disabled="selfNominateAll"
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
                        :isDisabled="selfNominateAll"
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
                            :isDisabled="selfNominateAll"
                        />
                        </div>
                    </template>
                </VueScrollingTable>
            </div>
            <div class="h-[10%]">
                <div class="flex items-center space-x-2 py-2 h-1/2">
                    <input type="checkbox"
                        class="h-8 w-8"
                        v-model="selfNominateAll"
                        @click="handleSelfNominateAll()"    
                    />
                    <p>This period of leave will not affect my ability to handle all my responsibilities and as such, no nominations are required.</p>
                </div>
                <div class="flex justify-between h-1/2 space-x-16">
                    <button class="py-2 bg-red-500 rounded-md text-white font-bold text-2xl w-1/2"
                        @click="cancelApplication()"
                    >
                        Cancel Application
                    </button>
                    <button class="py-2 bg-green-500 rounded-md text-white font-bold text-2xl w-1/2"
                        @click="submitApplication()"
                    >
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