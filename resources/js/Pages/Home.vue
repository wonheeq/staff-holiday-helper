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

let fetchWelcomeMessageData = async() => {
    try {
        const resp = await axios.get("/api/getWelcomeMessageData/" + user.value.accountNo);
        welcomeData = resp.data;
        dataReady.value = true;
    } catch (error) {
        alert("Failed to load data: Please try again");
        console.log(error);
    }
}

let showNominationModal = ref(false);
let nominationModalData = reactive([]);
let roles = reactive([]);
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
        alert("Failed to load data: Please try again");
        console.log(error);
    }
}; 

function handleCloseNominations() {
    roles = [];
    nominationModalData = [];
    showNominationModal.value = false;
}

let showReviewAppModal = ref(false);
let reviewAppModalData = reactive([
]);
async function handleReviewApplication(message) {
    await fetchApplicationForReview(message);
    showReviewAppModal.value = true;
}

let fetchApplicationForReview = async(message) => {
    try {
        const resp = await axios.get('/api/getApplicationForReview/' + user.value.accountNo + "/" + message.applicationNo);
        reviewAppModalData = resp.data;
        reviewAppModalData.message = message;
    } catch (error) {
        alert("Failed to load data: Please try again");
        console.log(error);
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
</script>

<template>
    <PageLayout>
        <AuthenticatedLayout>
            <div v-if="isMobile">
                <div class="flex screen-mobile mx-2 my-2" v-show="!calendarLarge">
                    <div class="flex flex-col w-full" v-if="dataReady">
                        <HomeShortcuts :welcomeData="welcomeData" class="w-full" />
                        <CalendarSmall
                            class="flex drop-shadow-md mt-2"
                            @enlarge-calendar="calendarLarge=true"    
                        />
                        <HomeMessages
                            class="mt-2 drop-shadow-md"
                            @acceptSomeNominations="(message) => handleAcceptSomeNominations(message)"
                            @reviewApplication="(message) => handleReviewApplication(message)"
                        ></HomeMessages>
                    </div>
                </div>
                <CalendarLarge
                    class="screen-mobile mx-2 mt-2 drop-shadow-md"
                    v-show="calendarLarge"
                    @shrink-calendar="calendarLarge=false"
                />
            </div>
            <div v-else>
                <div class="flex screen mx-4 my-4" v-show="!calendarLarge">
                    <div class="flex flex-col items-center w-4/5 1440:w-10/12 mr-4" v-if="dataReady">
                        <HomeShortcuts :welcomeData="welcomeData" class="h-3/6 min-w-[800px] 1080:h-2/5 1440:h-2/5 4k:h-[35%] w-3/5 1080:w-1/2"></HomeShortcuts>
                        <HomeMessages
                            class="h-3/6 1080:h-3/5 1440:h-3/5 4k:h-[65%] mt-4 drop-shadow-md"
                            @acceptSomeNominations="(message) => handleAcceptSomeNominations(message)"
                            @reviewApplication="(message) => handleReviewApplication(message)"
                        ></HomeMessages>
                    </div>
                    <CalendarSmall
                        class="flex w-1/5 1440:w-2/12 drop-shadow-md"
                        @enlarge-calendar="calendarLarge=true"    
                    />
                </div>
                <CalendarLarge
                    class="screen mx-4 mt-4 drop-shadow-md"
                    v-show="calendarLarge"
                    @shrink-calendar="calendarLarge=false"
                />
            </div>
            <Teleport to="body">
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

<style>
.screen {
    height: calc(93vh - 3rem);
}
.screen-mobile {
    /* mobile screen uses 0.5rem for margins */
    height: calc(93vh - 1.5rem);
}
</style>