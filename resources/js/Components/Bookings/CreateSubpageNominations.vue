<script setup>
import axios from "axios"; 
import { reactive, ref, computed, onMounted } from 'vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import Swal from 'sweetalert2'
import Nomination from "./Nomination.vue";
import NomineeDropdown from "@/Components/Bookings/NomineeDropdown.vue";
import { storeToRefs } from 'pinia';
import { useNominationStore } from '@/stores/NominationStore';
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const user = computed(() => page.props.auth.user);
let nominationStore = useNominationStore();
const { nominations, isSelfNominateAll } = storeToRefs(nominationStore);
const { fetchNominations } = nominationStore;

let props = defineProps({
    isEditing: {
        type: Boolean,
        default: false,
    },
    applicationNo: Number
});
let emit = defineEmits(['resetFields', 'submitApplication']);

let deadAreaColor = "#FFFFFF";

let selfNominateAll = isSelfNominateAll;
let allSelected = ref(false);
let roleFilter = ref("");
let staffMembers = reactive([]);


let fetchStaffMembers = async() => {
    try {
        const resp = await axios.get('/api/getBookingOptions/' + user.value.accountNo);
        staffMembers = resp.data;
    } catch (error) {
        alert("Failed to load data: Please try again");
        console.log(error);
    }
}; 

const dataReady = ref(false);

onMounted(async () => {
    if (!props.isEditing) {
        await fetchNominations(user.value.accountNo);
    }
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

function handleSingleNominationSelected(value) {
    if (!value) {
        // value was false
        // Check if all other nominations were selected
        if (nominations.value.filter(nom => nom.selected).length == nominations.value.length - 1) {
            allSelected.value = true;
        }
    }
    else {
        if (allSelected.value) {
            allSelected.value = false;
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
        allSelected.value = false;
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
    allSelected.value = false;
    selfNominateAll.value = false;
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
        'accountNo': user.value.accountNo,
        'selfNominateAll': selfNominateAll.value,
    }

    // pass data to parent to handle
    emit('submitApplication', data);

    allSelected.value = false;
    selfNominateAll.value = false;
}

const disabledClass = "bg-gray-300 border-gray-100";
</script>
<template>
    <div class="flex flex-col w-full justify-between laptop:pageHeight" v-if="dataReady">
        <div class="flex flex-col w-full h-[10%] pb-2 laptop:pb-0">
            <p class="laptop:text-lg 1080:text-2xl 1440:text-4xl 4k:text-5xl">
                Nominate Substitutes:
            </p>
            <div class="flex w-full justify-between 4k:py-6">
                <div class="flex space-x-1 laptop:space-x-4">
                    <div class="flex flex-col items-center">
                        <p class="text-xs 1080:text-base 1440:text-xl 4k:text-3xl">
                            Select
                        </p>
                        <input type="checkbox"
                            class="w-4 h-4 1080:w-6 1080:h-6 1440:w-8 1440:h-8 4k:h-12 4k:w-12"
                            :class="selfNominateAll ? disabledClass : ''"
                            v-model="allSelected"
                            @change="handleSelectAll()"    
                            :disabled="selfNominateAll"
                        />
                    </div>
                    <div class="flex flex-col w-[10rem] laptop:w-[12rem] 1080:w-[22rem] 1440:w-[31rem] 4k:w-[48rem]">
                        <p class="text-xs 1080:text-base 1440:text-xl 4k:text-3xl w-full">
                            Filter Roles
                        </p>
                        <input type="text"
                            class="h-4 1080:h-6 1440:h-8 4k:h-12 w-full text-xs 1080:text-sm 1440:text-base 4k:text-2xl"
                            :class="selfNominateAll ? disabledClass : ''"
                            v-model="roleFilter"
                            :disabled="selfNominateAll"
                        />
                    </div>
                </div>
                <div class="laptop:w-[11rem] 1080:w-[17rem] 1440:w-[20rem] 4k:w-[32rem]"></div>
                <div class="flex flex-col mr-2.5 items-end w-[8rem] laptop:w-[12rem] 1080:w-[17rem] 1440:w-[22rem] 4k:w-[32rem]">
                    <p class="text-xs 1080:text-base 1440:text-xl 4k:text-3xl w-full">
                        Select Substitute ({{ numSelectedNominations }}):
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
        <div class="h-[1%]"></div>
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
                        @nominationSelected="(value) => handleSingleNominationSelected(value)"
                    />
                    </div>
                </template>
            </VueScrollingTable>
        </div>
        <div class="flex flex-col h-[10%] justify-between">
            <div class="flex items-center space-x-2 py-2 laptop:pb-2 laptop:pt-0">
                <input type="checkbox"
                    class="w-4 1080:w-6 h-4 1080:h-6 1440:w-8 1440:h-8 4k:h-12 4k:w-12"
                    v-model="selfNominateAll"
                    @click="handleSelfNominateAll()"    
                />
                <p class="text-xs 1080:text-sm 1440:text-base 4k:text-2xl ">
                    I will handle all my responsibilities for this period of leave, therefore no nominations are required.
                </p>
            </div>
            <div class="flex justify-between h-3/4 space-x-16">
                <button class="bg-red-500 rounded-md text-white font-bold 1080:text-xl 1440:text-2xl 4k:text-4xl text-center w-1/2"
                    @click="cancelApplication()"
                    v-if="!props.isEditing"
                >
                    Cancel Application
                </button>
                <button class="bg-red-500 rounded-md text-white font-bold 1080:text-xl 1440:text-2xl 4k:text-4xl text-center w-1/2"
                    @click="cancelApplication()"
                    v-if="props.isEditing"
                >
                    Cancel Edit
                </button>
                <button class="bg-green-500 rounded-md text-white font-bold 1080:text-xl 1440:text-2xl 4k:text-4xl text-center w-1/2"
                    @click="submitApplication()"
                    v-if="!props.isEditing"
                >
                    Submit Application
                </button>
                <button class="bg-green-500 rounded-md text-white font-bold 1080:text-xl 1440:text-2xl 4k:text-4xl text-center w-1/2"
                    @click="submitApplication()"
                    v-if="props.isEditing"
                >
                    Submit Edit
                </button>
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
    height: calc(0.77 * 0.92 * 0.95 * (93vh - 3rem) - 2rem);
}
</style>