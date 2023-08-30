<script setup>
import Swal from 'sweetalert2';
import axios from 'axios';
import { storeToRefs } from 'pinia';
import { useUserStore } from '@/stores/UserStore';
let userStore = useUserStore();
const { userId } = storeToRefs(userStore);
let props = defineProps({ source: Object });


function handleAccept() {
    let data = {
        'accountNo': props.source.accountNo,
        'applicationNo': props.source.applicationNo,
        'processedBy': null
    };
    axios.post('/api/acceptApplication', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to accept application, please try again.',
                    text: res.message
                });
            }
            else {
                // Set application status to Y
                props.source.status = 'Y';
                props.source.updated_at = new Date();
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to accept applications, please try again.',
        });
    });
}

function handleReject() {
    let data = {
        'accountNo': props.source.accountNo,
        'applicationNo': props.source.applicationNo,
        'rejectReason': props.source.rejectReason,
        'processedBy' : null
    };
    axios.post('/api/rejectApplication', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to accept application, please try again.',
                    text: res.message
                });
            }
            else {
                // Set application status to 'N'
                props.source.status = 'N';
                props.source.updated_at = new Date();
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to reject applications, please try again.',
        });
    });
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
                    <p class="text-2xl" v-if="nomination.nomineeNo != userId">
                        → {{ nomination.name }} ({{ nomination.nomineeNo }}) - {{ nomination.task }} - {{ nomination.nomineeNo }}@curtin.edu.au
                    </p>
                </div>
            </div>
        </div>  
        <div class="flex flex-col w-1/6 bg-gray-200 text-3xl ml-2 p-2 justify-center items-center">
            <button class="flex flex-col items-center p-11"
                @click="handleAccept()"
            >
            <img src="/images/accept.svg" style="width: 100px; height: 100px;"/>
            <p class="text-sm 1440:text-lg">Accept</p>
            </button>
        </div>
        <div class="flex flex-col w-1/6 bg-gray-200 text-3xl ml-2 p-2 justify-center items-center">
            <button class="flex flex-col items-center"
                @click="handleReject()"
            >
                <img src="/images/reject.svg" style="width: 100px; height: 100px;"/>
                <p class="text-sm 1440:text-lg">Reject</p>
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
                    <p class="text-2xl" v-if="nomination.nomineeNo != userId">
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
</template>

<style>
p.right{
    text-align: right;
    font-size: 2xl;
}
</style>