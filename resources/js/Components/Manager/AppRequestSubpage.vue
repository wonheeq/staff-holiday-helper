<script setup>
import ApplicationInfo from "@/Components/Manager/ApplicationInfo.vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { computed, onMounted, reactive, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ReviewApplication from '@/Components/ReviewApplication.vue';
import { useDark } from "@vueuse/core";
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const isDark = useDark();
const page = usePage();
const user = computed(() => page.props.auth.user);
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);

let applicationStore = useApplicationStore();
const { viewing, managerApplications } = storeToRefs(applicationStore);
const { fetchManagerApplications } = applicationStore;

let reviewAppModalData = reactive([]);
let showReviewAppModal = ref(false);
    
async function handleReviewApplication(appNo) {
    let response = await fetchApplicationForReview(appNo);
    showReviewAppModal.value = response;
}
let fetchApplicationForReview = async(appNo) => {
    await axios.get('/api/getApplicationForReview/' + user.value.accountNo + "/" + appNo)
    .then (resp => {
        reviewAppModalData = resp.data;
        return true;
    })
    .catch (error => {
        reviewAppModalData = [];
        Swal.fire({
            icon: 'error',
            title: 'Failed to review application',
            text: 'Invalid permissions to review application'
        });
        console.log(error);
        return false;
    });
}; 

function handleCloseReviewApp() {
    reviewAppModalData = [];
    showReviewAppModal.value = false;
    fetchManagerApplications(user.value.accountNo);
}



onMounted(async () => {
    fetchManagerApplications(user.value.accountNo);
})

const deadAreaColor = computed(() => {
    return isDark.value ? '#1f2937': '#FFFFFF';
});

const appCount = computed(() => {
    if (managerApplications.value.length > 0) {
        if (viewing.value == 'all') {
            return managerApplications.value.length;
        }
        else if(viewing.value == 'accepted'){
            return managerApplications.value.filter(application => application.status === 'Y').length;
        }
        else if(viewing.value == 'rejected'){
            return managerApplications.value.filter(application => application.status === 'N').length;
        }
        else{
            return managerApplications.value.filter(application => application.status === 'U').length;
        }
    }
    return 0;
});
</script>
<template>
    <div v-if="isMobile" class="subpage-height-mobile w-full" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="h-[5%]">
            <p class="font-bold text-2xl p-2">
                Leave Applications ({{ appCount }}): 
            </p>
        </div>
        <div class="h-[3%] text-xs flex items-center">
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
        <div v-if="managerApplications" class="h-[92%] pb-1" :class="isDark?'bg-gray-800':'bg-white'">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
                :class="isDark?'scrollbar-dark':''"
            >
                <template #tbody>
                    <div v-for="item in managerApplications" :key="item.id" class="mb-1"
                        v-show="viewing == 'all'
                        || (item.status == 'Y' && viewing == 'accepted')
                        || (item.status == 'N' && viewing == 'rejected')
                        || (item.status == 'U' && viewing == 'unAcknowledged')
                    ">
                        <ApplicationInfo
                            @reviewApplication="handleReviewApplication(item.applicationNo)"
                            :source="item"
                        ></ApplicationInfo>
                       
                    </div>
                </template>
            </VueScrollingTable>
        </div>
    </div>
    <div v-else class="subpage-height w-full" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="h-[7%]">
            <p class="font-bold p-4 text-2xl laptop:text-base 1080:text-3xl 1440:text-5xl 4k:text-7xl">
                Leave Applications ({{ appCount }}): 
            </p>
        </div>
        <div class="h-[5%] mx-1 text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-5xl laptop:mx-2 1080:mx-2 1440:mx-5 4k:mx-5 flex items-center">
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
        <div v-if="managerApplications" class="h-[88%] mx-2 1440:mx-4 scroller pb-4" :class="isDark?'bg-gray-800':'bg-white'">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
                :class="isDark?'scrollbar-dark':''"
            >
                <template #tbody>
                    <div v-for="item in managerApplications" :key="item.id" class="mb-2"
                        v-show="viewing == 'all'
                        || (item.status == 'Y' && viewing == 'accepted')
                        || (item.status == 'N' && viewing == 'rejected')
                        || (item.status == 'U' && viewing == 'unAcknowledged')
                    ">
                        <ApplicationInfo
                            @reviewApplication="handleReviewApplication(item.applicationNo)"
                            :source="item"
                        ></ApplicationInfo>
                       
                    </div>
                </template>
            </VueScrollingTable>
        </div>
    </div>
    <Teleport to="#modals">
        <ReviewApplication
            v-if="showReviewAppModal"
            :data="reviewAppModalData"
            @close="handleCloseReviewApp()"
        />
    </Teleport>
</template>
<style>
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

