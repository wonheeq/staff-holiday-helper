<script setup>
import ApplicationInfo from "@/Components/Manager/ApplicationInfo.vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { onMounted } from 'vue';

let applicationStore = useApplicationStore();
const { filteredApplications, viewing, applications } = storeToRefs(applicationStore);
const { fetchManagerApplications } = applicationStore;

onMounted(() => {
    fetchManagerApplications();
});

let deadAreaColor = "#FFFFFF";
</script>
<template>
    <div class="subpage-height w-full">
        <div class="h-[7%]">
            <p class="font-bold text-5xl">
                Leave Applications:
            </p>
        </div>
        <div class="h-[5%] mx-5">
            <input 
                type="radio" 
                id="allApplications" 
                name="applicationFilter" 
                class="filter-radio" 
                value="all"
                v-model="viewing"
                checked>
            <label for="allApplications" class="filter-label pr-4">All Applications</label>
        
            <input 
                type="radio" 
                id="unAcknowledged" 
                name="applicationFilter" 
                class="filter-radio"
                value="unAcknowledged"
                v-model="viewing"
                >
            <label for="unAcknowledged" class="filter-label pr-4">Unacknowledged Applications</label>

            <input 
                type="radio" 
                id="accepted" 
                name="applicationFilter" 
                class="filter-radio"
                value="accepted"
                v-model="viewing"
                >
            <label for="accepted" class="filter-label pr-4">Accepted Applications</label>

            <input 
                type="radio" 
                id="rejected" 
                name="applicationFilter" 
                class="filter-radio"
                value="rejected"
                v-model="viewing"
                >
            <label for="rejected" class="filter-label pr-4">Rejected Applications</label>
        </div>
        <div class="bg-white mx-2 mb-2 1440:mx-4 1440:mb-4 scroller">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div v-for="item in filteredApplications" :key="item.id" class="mb-2">
                        <ApplicationInfo
                            :source="item"
                        ></ApplicationInfo>
                    </div>
                    
                </template>
            </VueScrollingTable>
        </div>
    </div>
</template>
<style>
.subpage-height {
    height: calc(0.95 * (93vh - 3rem));
}
.scroller {
    overflow-y: auto;
    height: 88%;
  }
.filter-radio {
    transform: scale(1.5);
    margin-right: 10px;
}
.filter-label {
    font-size: 18px; 
}
.hide {
    display: none;
}
</style>

