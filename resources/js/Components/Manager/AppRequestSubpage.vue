<script setup>
import ApplicationInfo from "@/Components/Manager/ApplicationInfo.vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
const page = usePage();
import {computed} from 'vue';
const user = computed(() => page.props.auth.user);
import { useDark } from "@vueuse/core";
const isDark = useDark();
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
// const { $isMobile() } = storeToRefs(screenSizeStore);


let applicationStore = useApplicationStore();
const { filteredApplications, viewing } = storeToRefs(applicationStore);
const { fetchManagerApplications } = applicationStore;
const dataReady = ref(false);

onMounted(async () => {
    await fetchManagerApplications(user.value.accountNo);
    dataReady.value = true;
});

const totalApplications = Object.keys(filteredApplications).length;
const deadAreaColor = computed(() => {
    return isDark.value ? '#1f2937': '#FFFFFF';
})
</script>
<template>
    <div v-if="$isMobile()" class="subpage-heightMobile2 w-full" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="h-[5%]">
            <p class="font-bold text-2xl">
                Leave Applications ({{ (Object.keys(filteredApplications).length) }}): 
            </p>
        </div>
        <div class="h-[3%] text-xs">
            <input 
                type="radio" 
                id="allApplications" 
                name="applicationFilter" 
                class="filter-radio" 
                value="all"
                v-model="viewing"
                checked>
            <label for="allApplications" class="filter-text ">All</label>
        
            <input 
                type="radio" 
                id="unAcknowledged" 
                name="applicationFilter" 
                class="filter-radio"
                value="unAcknowledged"
                v-model="viewing"
                >
            <label for="unAcknowledged" class="filter-text">Unacknowledged</label>
            <input 
                type="radio" 
                id="accepted" 
                name="applicationFilter" 
                class="filter-radio"
                value="accepted"
                v-model="viewing"
                >
            <label for="accepted" class="filter-text">Accepted</label>

            <input 
                type="radio" 
                id="rejected" 
                name="applicationFilter" 
                class="filter-radio"
                value="rejected"
                v-model="viewing"
                >
            <label for="rejected" class="filter-text">Rejected</label>
        </div>
        <div v-if="dataReady" class="h-[92%] pb-1" :class="isDark?'bg-gray-800':'bg-white'">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
                :class="isDark?'scrollbar-dark':''"
            >
                <template #tbody>
                    <div v-for="item in filteredApplications" :key="item.id" class="mb-1">
                        <ApplicationInfo
                            :source="item"
                        ></ApplicationInfo>
                    </div>
                    
                </template>
            </VueScrollingTable>
        </div>
    </div>
    <div v-else class="subpage-height w-full" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="h-[7%]">
            <p class="font-bold text-2xl laptop:text-base 1080:text-3xl 1440:text-5xl 4k:text-7xl">
                Leave Applications ({{ (Object.keys(filteredApplications).length) }}): 
            </p>
        </div>
        <div class="h-[5%] mx-1 text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-5xl laptop:mx-2 1080:mx-2 1440:mx-5 4k:mx-5">
            <input 
                type="radio" 
                id="allApplications" 
                name="applicationFilter" 
                class="filter-radio" 
                value="all"
                v-model="viewing"
                checked>
            <label for="allApplications" class="filter-text ">All</label>
        
            <input 
                type="radio" 
                id="unAcknowledged" 
                name="applicationFilter" 
                class="filter-radio"
                value="unAcknowledged"
                v-model="viewing"
                >
            <label for="unAcknowledged" class="filter-text">Unacknowledged</label>

            <input 
                type="radio" 
                id="accepted" 
                name="applicationFilter" 
                class="filter-radio"
                value="accepted"
                v-model="viewing"
                >
            <label for="accepted" class="filter-text">Accepted</label>

            <input 
                type="radio" 
                id="rejected" 
                name="applicationFilter" 
                class="filter-radio"
                value="rejected"
                v-model="viewing"
                >
            <label for="rejected" class="filter-text">Rejected</label>
        </div>
        <div v-if="dataReady" class="h-[88%] mx-2 1440:mx-4 1440:mb-4 scroller pb-2" :class="isDark?'bg-gray-800':'bg-white'">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
                :class="isDark?'scrollbar-dark':''"
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
    height: calc(0.95 * 93vh - 3rem);
}
.subpage-heightMobile2 {
    height: calc(0.95 * 93vh - 1.5rem);
}
.filter-radio {
    transform: scale(0.6);
    margin-right: 2px;
}
.filter-text{
    padding-right: 1px;
}
/* 1080p */
@media 
(min-width: 1920px) {
    .filter-radio {
        transform: scale(1.0);
        margin-right: 10px;
    }
    .filter-text{
        padding-right: 10px;
    }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .filter-radio {
        transform: scale(1.5);
        margin-right: 10px;
    }
    .filter-text{
        padding-right: 20px;
    }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .filter-radio {
        transform: scale(2.0);
        margin-right: 10px;
    }
    .filter-text{
        padding-right: 4;
    }
}
.hide {
    display: none;
}
</style>

