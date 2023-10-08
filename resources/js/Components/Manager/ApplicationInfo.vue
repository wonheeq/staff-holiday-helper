<script setup>
import ReviewApplication from '@/Components/ReviewApplication.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { storeToRefs } from 'pinia';
import { usePage } from '@inertiajs/vue3'
import {computed, reactive, ref} from 'vue';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { useDark } from "@vueuse/core";
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const isDark = useDark();
let applicationStore = useApplicationStore();
const { fetchManagerApplications } = applicationStore;

const page = usePage();
const user = computed(() => page.props.auth.user);


let props = defineProps({ source: Object });
let reviewAppModalData = reactive([]);
let showReviewAppModal = ref(false);
    
async function handleReviewApplication() {
    let response = await fetchApplicationForReview();
    showReviewAppModal.value = response;
}
let fetchApplicationForReview = async() => {
    try {
        const resp = await axios.get('/api/getApplicationForReview/' + user.value.accountNo + "/" + props.source.applicationNo);
        reviewAppModalData = resp.data;
        return true;
    } catch (error) {
        reviewAppModalData = [];
        Swal.fire({
            icon: 'error',
            title: 'Failed to review application',
            text: 'Invalid permissions to review application'
        });
        console.log(error);
        return false;
    }
}; 

function handleCloseReviewApp() {
    reviewAppModalData = [];
    showReviewAppModal.value = false;
    fetchManagerApplications(user.value.accountNo);
}


</script>
<template>
    <div v-if="isMobile">
        <!-- Render for undecided applications -->
        <div v-if="source.status == 'U'" class="mr-4 " :class="isDark?'bg-gray-800':'bg-white'">
            <div class="w-full p-2" :class="isDark?'bg-gray-700':'bg-gray-200'">
                <div>
                    <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">{{ source.applicantName }} ({{ source.accountNo }}) has applied for leave from {{ source.sDate }} to {{ source.eDate }}</p>
                    <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl ml-auto pr-5 pt-2 pb-2">Applicant email: <span class="underline">{{ source.accountNo }} @curtin.edu.au</span></p>
                </div>
                <div>
                    <p class="pt-2 text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">Substitutes (The following staffs have agreed to substitute for the following roles):</p>
                    <div v-if="!source.isSelfNominatedAll" v-for="nomination in source.nominations">
                        <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl" v-if="nomination.nomineeNo != user.accountNo">
                            → {{ nomination.name }} ({{ nomination.nomineeNo }}) - {{ nomination.task }} - {{ nomination.nomineeNo }}@curtin.edu.au
                        </p>
                    </div>
                </div>
            </div>          
            <div class="w-full text-3xl flex justify-center items-center pt-2" :class="isDark?'bg-gray-700':'bg-gray-200'">
                <button
                    @click="handleReviewApplication()"
                    class="flex flex-col items-center"
                >
                    <img src="/images/review-app.svg" 
                    class="review" 
                    :class="isDark?'darkModeImage':''"
                    />
                    <p class="text-sm">Review</p>
                </button>
            </div>
        </div>
    
        <!-- Render for reviewed applications -->
        <div v-if="source.status == 'Y' || source.status == 'N'" class="mr-4" :class="isDark?'bg-gray-800':'bg-white'">
            <div class="w-full p-2 " :class="isDark?'bg-gray-700':'bg-gray-200'">
                <div>
                    <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">{{ source.applicantName }} ({{ source.accountNo }}) has applied for leave from {{ source.sDate }} to {{ source.eDate }}</p>
                    <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl ml-auto pr-5 pt-2 pb-2">Applicant email: <span class="underline">{{ source.accountNo }} @curtin.edu.au</span></p>
                </div>
                <div>
                    <p class="pt-2 text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">Substitutes (The following staffs have agreed to substitute for the following roles):</p>
                    <div v-if="!source.isSelfNominatedAll" v-for="nomination in source.nominations">
                        <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl" v-if="nomination.nomineeNo != user.accountNo">
                            → {{ nomination.name }} ({{ nomination.nomineeNo }}) - {{ nomination.task }} - {{ nomination.nomineeNo }}@curtin.edu.au
                        </p>
                    </div>
                </div>
            </div>  
            <div class="w-full text-3xl p-2" :class="isDark?'bg-gray-700':'bg-gray-200'">
                <p v-if="source.status ==='Y'" class="text-center text-sm pr-1 laptop:text-base 1080:text-2xl 1440:text-3xl 4k:text-4xl ml-auto text-green-500 laptop:pr-10 1080:pr-10  1440:pr-10  4k:pr-10 " style="margin-top: auto;">
                    <strong>APPROVED</strong>
                </p>
                <p v-if="source.status ==='N'" class="text-center text-sm pr-1 laptop:text-base 1080:text-2xl 1440:text-3xl 4k:text-4xl ml-auto text-red-500 laptop:pr-10 1080:pr-10 1440:pr-10  4k:pr-10" style="margin-top: auto;">
                    <strong>DENIED</strong>
                </p>
                <p v-if="source.status ==='N'" class="text-xs text-center laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl ml-auto text-red-500 laptop:pr-10 1080:pr-10 1440:pr-10  4k:pr-10">
                    Reason: {{ source.rejectReason }}.
                </p>
            </div>
        </div>
    </div>
    <div v-else>
        <!-- Render for undecided applications -->
        <div v-if="source.status == 'U'" class="flex flex-row mr-4" :class="isDark?'bg-gray-800':'bg-white'">
            <div  class="flex flex-col w-5/6 p-2" :class="isDark?'bg-gray-700':'bg-gray-200'">
                <div class="flex flex-row">
                    <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">{{ source.applicantName }} ({{ source.accountNo }}) has applied for leave from {{ source.sDate }} to {{ source.eDate }}</p>
                </div>
                <div class="flex flex-row">
                    <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl ml-auto pr-5">Applicant email: <span class="underline">{{ source.accountNo }} <br>@curtin.edu.au</span></p>
                </div>
                <div>
                    <p class="pt-2 text-sm laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">Substitutes (The following staffs have agreed to substitute for the following roles):</p>
                    <div v-if="!source.isSelfNominatedAll" v-for="nomination in source.nominations">
                        <p class="text-sm laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl" v-if="nomination.nomineeNo != user.accountNo">
                            → {{ nomination.name }} ({{ nomination.nomineeNo }}) - {{ nomination.task }} - {{ nomination.nomineeNo }}@curtin.edu.au
                        </p>
                    </div>
                </div>
            </div>          
            <div class="flex flex-col w-1/6 text-3xl ml-2 p-2 justify-center items-center" :class="isDark?'bg-gray-700':'bg-gray-200'">
                <button class="flex flex-col items-center"
                    @click="handleReviewApplication()"
                >
                    <img src="/images/review-app.svg" 
                    class="review"
                    :class="isDark?'darkModeImage':''"/>
                    <p class="text-sm 1440:text-lg">Review</p>
                </button>
            </div>
        </div>
    
        <!-- Render for reviewed applications -->
        <div v-if="source.status == 'Y' || source.status == 'N'" class="flex flex-row mr-4" :class="isDark?'bg-gray-800':'bg-white'">
            <div  class="flex flex-col w-full p-2 " :class="isDark?'bg-gray-700':'bg-gray-200'">
                <div class="flex flex-row pb-8">
                    <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">{{ source.applicantName }} ({{ source.accountNo }}) has applied for leave from {{ source.sDate }} to {{ source.eDate }}</p>
                </div>
                <div>
                    <p class="pt-2 text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">Substitutes (The following staffs have agreed to substitute for the following roles):</p>
                    <div v-if="!source.isSelfNominatedAll" v-for="nomination in source.nominations">
                        <p class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl" v-if="nomination.nomineeNo != user.accountNo">
                            → {{ nomination.name }} ({{ nomination.nomineeNo }}) - {{ nomination.task }} - {{ nomination.nomineeNo }}@curtin.edu.au
                        </p>
                    </div>
                </div>
            </div>  
            <div class="flex flex-col w-2/5 text-3xl p-2" :class="isDark?'bg-gray-700':'bg-gray-200'">
                <p class="text-xs pr-10 pt-7 ml-auto laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl laptop:pt-8 1080:pt-8  1440:pt-8  4k:pt-8 ">Applicant email: <span class="underline">{{ source.accountNo }}@curtin.edu.au</span></p>
                <p v-if="source.status ==='Y'" class="text-sm pr-1 pb-7 laptop:text-base 1080:text-2xl 1440:text-3xl 4k:text-4xl ml-auto text-green-500 laptop:pr-10 1080:pr-10  1440:pr-10  4k:pr-10 " style="margin-top: auto;">
                    <strong>APPROVED</strong>
                </p>
                <p v-if="source.status ==='N'" class="text-sm pr-1 laptop:text-base 1080:text-2xl 1440:text-3xl 4k:text-4xl ml-auto text-red-500 laptop:pr-10 1080:pr-10 1440:pr-10  4k:pr-10" style="margin-top: auto;">
                    <strong>DENIED</strong>
                </p>
                <p v-if="source.status ==='N'" class="text-xs text-right laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl ml-auto text-red-500 laptop:pr-10 1080:pr-10 1440:pr-10  4k:pr-10">
                    Reason: {{ source.rejectReason }}.
                </p>
            </div>
        </div>
    </div>
    
    <Teleport to="body">
        <ReviewApplication
            v-show="showReviewAppModal"
            :data="reviewAppModalData"
            @close="handleCloseReviewApp()"
        />
    </Teleport>

</template>

<style>
@media
(min-width: 1360px) {
    .review{
        height: 100;
        width: 100px;
    }
}
/* 1080p */
@media
(min-width: 1920px) {
    .review {
        height: 100px;
        width: 100px;
    }
}
/* 1440p */
@media
(min-width: 2560px) {
    .review {
        height: 100px;
        width: 100px;
    }
}
/* 2160p */
@media
(min-width: 3840px) {
    .review {
        height: 100px;
        width: 100px;
    }
}
</style>