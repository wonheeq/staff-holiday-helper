<script setup>
import LoggedInView from "@/Components/LoggedInView.vue";
import SubpageNavbar from "@/Components/SubpageNavbar.vue";
import BookingsMessage from "@/Components/BookingsMessage.vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { useApplicationStore } from '@/stores/ApplicationStore';
import { onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
let applicationStore = useApplicationStore();
const { applications } = storeToRefs(applicationStore);
const { fetchApplications } = applicationStore;

onMounted(() => {
    fetchApplications();
});
const options = [
    { id: 'applications', title: 'Applications'},
    { id: 'create', title: 'Create New Application'},
    { id: 'subs', title: 'Your Substitutions'},
];
let deadAreaColor = "#FFFFFF";
let activeScreen = ref("applications");
const subpageClass = "p-4 rounded-bl-md rounded-br-md rounded-tr-md bg-white h-[95%]";
</script>

<template>
    <LoggedInView>
        <div class="flex flex-col screen mt-4 mx-4 drop-shadow-md">
            <SubpageNavbar
                class="h-[5%]"
                :options="options"
                @screen-changed="screen => activeScreen = screen"
            />
            <VueScrollingTable
                v-show="activeScreen === 'applications'" 
                :class="subpageClass"
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div v-for="item in applications" :key="item.id" class="mb-2">
                        <BookingsMessage :source="item"></BookingsMessage>
                    </div>
                </template>
            </VueScrollingTable>
            <div
                v-show="activeScreen === 'create'" 
                :class="subpageClass"
            >
                Create subpage
            </div>
            <div
                v-show="activeScreen === 'subs'" 
                :class="subpageClass"
            >
                Sub subpage
            </div>
        </div>
    </LoggedInView>
</template>

<style>
.screen {
    height: calc(93vh - 3rem);
}
</style>