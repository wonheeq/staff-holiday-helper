<script setup>
import ReviewApplication from '@/Components/ReviewApplication.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3'
const page = usePage();
import {computed, reactive, ref} from 'vue';
const user = computed(() => page.props.auth.user);

let props = defineProps({ source: Object });
let reviewAppModalData = reactive([]);
let showReviewAppModal = ref(false);
    
let fetchApplicationForReview = async() => {
    try {
        const resp = await axios.get('/api/getApplicationForReview/' + user.value.accountNo + "/" + props.source.applicationNo);
        reviewAppModalData = resp.data;
    } catch (error) {
        alert("Failed to load data: Please try again");
        console.log(error);
    }
}; 
function handleCloseReviewApp() {
    reviewAppModalData = [];
    showReviewAppModal.value = false;
}

async function handleReviewApplication() {
    await fetchApplicationForReview();
    showReviewAppModal.value = true;
}
</script>
<template>
    <!-- Render for undecided applications -->
    <div v-if="source.status == 'U'" class="flex flex-row bg-white mr-4">
        <div  class="flex flex-col w-5/6 bg-gray-200 p-2">
            <div class="flex flex-row">
                <p class="text-2xl">{{ source.applicantName }} ({{ source.accountNo }}) has applied for leave from {{ source.sDate }} to {{ source.eDate }}</p>
            </div>
            <div class="flex flex-row">
                <p class="text-2xl ml-auto pr-5">Applicant email: <span class="underline">{{ source.accountNo }}@curtin.edu.au</span></p>
            </div>
            <div>
                <p class="pt-2 text-2xl">Substitutes (The following staffs have agreed to substitute for the following roles):</p>
                <div v-if="!source.isSelfNominatedAll" v-for="nomination in source.nominations">
                    <p class="text-2xl" v-if="nomination.nomineeNo != user.accountNo">
                        → {{ nomination.name }} ({{ nomination.nomineeNo }}) - {{ nomination.task }} - {{ nomination.nomineeNo }}@curtin.edu.au
                    </p>
                </div>
            </div>
        </div>          
        <div class="flex flex-col w-1/6 bg-gray-200 text-3xl ml-2 p-2 justify-center items-center">
            <button class="flex flex-col items-center"
                @click="handleReviewApplication()"
            >
                <img src="/images/review-app.svg" style="width: 100px; height: 100px;"/>
                <p class="text-sm 1440:text-lg">Review</p>
            </button>
        </div>
    </div>

    <!-- Render for approve and unapprove applications -->
    <div v-if="source.status !== 'U'" class="flex flex-row bg-white mr-4">
        <div  class="flex flex-col w-full bg-gray-200 p-2 ">
            <div class="flex flex-row pb-8">
                <p class="text-2xl">{{ source.applicantName }} ({{ source.accountNo }}) has applied for leave from {{ source.sDate }} to {{ source.eDate }}</p>
            </div>
            <div>
                <p class="pt-2 text-2xl">Substitutes (The following staffs have agreed to substitute for the following roles):</p>
                <div v-if="!source.isSelfNominatedAll" v-for="nomination in source.nominations">
                    <p class="text-2xl" v-if="nomination.nomineeNo != user.accountNo">
                        → {{ nomination.name }} ({{ nomination.nomineeNo }}) - {{ nomination.task }} - {{ nomination.nomineeNo }}@curtin.edu.au
                    </p>
                </div>
            </div>
        </div>  
        <div class="flex flex-col w-2/5 bg-gray-200 text-3xl p-2">
            <p class="text-2xl ml-auto pr-10 pt-8">Applicant email: <span class="underline">{{ source.accountNo }}@curtin.edu.au</span></p>
            <p v-if="source.status ==='Y'" class="text-3xl ml-auto text-green-500 pr-10 pb-7" style="margin-top: auto;">
                <strong>APPROVED</strong>
            </p>
            <p v-if="source.status ==='N'" class="text-3xl ml-auto text-red-500 pr-10" style="margin-top: auto;">
                <strong>DENIED</strong>
            </p>
            <p v-if="source.status ==='N'" class="text-xl ml-auto text-red-500 pr-10">
                Reason: {{ source.rejectReason }}
            </p>
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
p.right{
    text-align: right;
    font-size: 2xl;
}
</style>