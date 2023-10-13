<script setup>
import PageLayout from "@/Layouts/PageLayout.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import HomeShortcuts from "@/Components/HomeShortcuts.vue";
import CalendarSmall from "@/Components/CalendarSmall.vue";
import CalendarLarge from "@/Components/CalendarLarge.vue";
import HomeMessages from "@/Components/HomeMessages.vue";
import AcceptSomeNominations from '@/Components/AcceptSomeNominations.vue';
import ReviewApplication from "@/Components/ReviewApplication.vue";
import axios from 'axios';
import Swal from "sweetalert2";
import { ref, reactive, computed } from "vue";
import { usePage } from '@inertiajs/vue3';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { useSubstitutionStore } from '@/stores/SubstitutionStore';
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const substitutionStore = useSubstitutionStore();
const { fetchSubstitutions } = substitutionStore;
let applicationStore = useApplicationStore();
const { fetchApplications } = applicationStore;
const page = usePage();
const user = computed(() => page.props.auth.user);

let welcomeData = reactive([]);
let dataReady = ref(false);

let showReviewAppModal = ref(false);
let reviewAppModalData = reactive([
]);

let showNominationModal = ref(false);
let nominationModalData = reactive([]);
let roles = reactive([]);

let fetchApplicationForReviewFromEmail = async(appNo) => {
    try {
        const resp = await axios.get('/api/getApplicationForReview/' + user.value.accountNo + "/" + appNo);
        reviewAppModalData = resp.data;
        console.log(reviewAppModalData);
        return true;
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: 'Error',
            text: "Unable to review application - did you review this application already?"
        });
        return false;
    }
};

let fetchMessageForApplication = async(appNo) => {
    try {
        const resp = await axios.get('/api/getMessageForApplication/' + user.value.accountNo + "/" + appNo);
        return resp.data;
    } catch (error) {
        console.log(error);
        return null;
    }
};

async function handleAcceptSomeNominationsFromEmail(appNo) {
    nominationModalData = await fetchMessageForApplication(appNo);
    await fetchRoles();
    showNominationModal.value = true;
}

let fetchWelcomeMessageData = async() => {
    try {
        const resp = await axios.get("/api/getWelcomeMessageData/" + user.value.accountNo);
        welcomeData = resp.data;
        dataReady.value = true;
    } catch (error) {
        //silently fail
        console.log(error);
    }
}
async function handleAcceptSomeNominations(message) {
    nominationModalData = message;
    await fetchRoles();
    showNominationModal.value = true;
}

let fetchRoles = async() => {
    try {
        let data = {
            'accountNo': user.value.accountNo,
            'applicationNo': nominationModalData.applicationNo,
        };
        const resp = await axios.post('/api/getRolesForNominee', data);
        roles = resp.data;
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: 'Failed to load data',
            text: 'Please try again later.'
        });
        console.log(error);
    }
};

function handleCloseNominations() {
    roles = [];
    nominationModalData = [];
    showNominationModal.value = false;
}

async function handleReviewApplication(message) {
    let shouldShow = await fetchApplicationForReview(message);
    showReviewAppModal.value = shouldShow;
}

let fetchApplicationForReview = async(message) => {
    try {
        const resp = await axios.get('/api/getApplicationForReview/' + user.value.accountNo + "/" + message.applicationNo);
        reviewAppModalData = resp.data;
        reviewAppModalData.message = message;
        return true;
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: 'Failed to load data',
            text: error.response.data['error']
        });
        return false;
    }
};

function handleCloseReviewApp() {
    reviewAppModalData = [];
    showReviewAppModal.value = false;
}

let calendarLarge = ref(false);
fetchWelcomeMessageData();
fetchApplications(user.value.accountNo);
fetchSubstitutions(user.value.accountNo);

// Error if insufficient permissions to visit a page
const customError =  page.props.errors;
if (customError != null) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: customError
    });
}

// Error if success message
const successMessage =  page.props.successMessage;
if (successMessage != null) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: successMessage
    });
}



//  if application to review
async function handleAppToReviewFromEmail(appNo) {
    let shouldShow = await fetchApplicationForReviewFromEmail(appToReview);
    if(shouldShow)
    {
        showReviewAppModal.value = true;
    }
}

const appToReview =  page.props.appToReview;
if (appToReview != null) {
    handleAppToReviewFromEmail(appToReview);
}


const nomsToReview =  page.props.nomsToReview;
if (nomsToReview != null) {
    handleAcceptSomeNominationsFromEmail(nomsToReview);
}
</script>

<template>
    <PageLayout>
        <AuthenticatedLayout>
            <div v-if="isMobile">
                <div class="flex screen-mobile mx-2 my-2" v-show="!calendarLarge" v-if="dataReady">
                    <div class="flex flex-col w-full">
                        <HomeShortcuts :welcomeData="welcomeData" class="w-full" />
                        <CalendarSmall
                            class="flex drop-shadow-md mt-2"
                            disableEnlarge
                        />
                        <HomeMessages
                            class="mt-2 drop-shadow-md"
                            @acceptSomeNominations="(message) => handleAcceptSomeNominations(message)"
                            @reviewApplication="(message) => handleReviewApplication(message)"
                        ></HomeMessages>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="flex screen mx-4 my-4" v-show="!calendarLarge"  v-if="dataReady">
                    <div class="flex flex-col items-center w-4/5 1080:4/6 1440:w-10/12 mr-4">
                        <HomeShortcuts :welcomeData="welcomeData" class="h-3/6 min-w-[400px] 1080:h-2/5 1440:h-2/5 4k:h-[35%] w-3/5 1080:w-1/2"></HomeShortcuts>
                        <HomeMessages
                            class="h-3/6 1080:h-3/5 1440:h-3/5 4k:h-[65%] mt-4 drop-shadow-md"
                            @acceptSomeNominations="(message) => handleAcceptSomeNominations(message)"
                            @reviewApplication="(message) => handleReviewApplication(message)"
                        ></HomeMessages>
                    </div>
                    <CalendarSmall
                        class="flex w-1/5 1080:2/6 1440:w-2/12 drop-shadow-md"
                        @enlarge-calendar="calendarLarge=true"
                    />
                </div>
                <CalendarLarge
                    class="screen mx-4 mt-4 drop-shadow-md"
                    v-show="calendarLarge"
                    @shrink-calendar="calendarLarge=false"
                />
            </div>
            <Teleport to="#modals">
                <AcceptSomeNominations
                    v-show="showNominationModal"
                    :data="nominationModalData"
                    :roles="roles"
                    @close="handleCloseNominations()"
                />
                <ReviewApplication
                    v-show="showReviewAppModal"
                    :data="reviewAppModalData"
                    @close="handleCloseReviewApp()"
                />
            </Teleport>
        </AuthenticatedLayout>
    </PageLayout>
</template>