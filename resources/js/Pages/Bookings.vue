<script setup>
import PageLayout from "@/Layouts/PageLayout.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SubpageNavbar from "@/Components/SubpageNavbar.vue";
import ApplicationsSubpage from '@/Components/Bookings/ApplicationsSubpage.vue';
import CreateSubpage from '@/Components/Bookings/CreateSubpage.vue';
import SubstitutionsSubpage from '@/Components/Bookings/SubstitutionsSubpage.vue';
import EditApplication from "@/Components/Bookings/EditApplication.vue";
import { ref, reactive, computed } from 'vue';
import { useNominationStore } from '@/stores/NominationStore';
import { useApplicationStore } from "@/stores/ApplicationStore";
import { usePage } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
// const { $isMobile() } = storeToRefs(screenSizeStore);
const page = usePage();
const user = computed(() => page.props.auth.user);
let nominationStore = useNominationStore();
const { fetchNominationsForApplicationNo } = nominationStore;
let applicationStore = useApplicationStore();
const { applications } = storeToRefs(applicationStore);

const options = [
    { id: 'apps', title: 'Applications', mobileTitle: 'Applications'},
    { id: 'create', title: 'Create New Application', mobileTitle: 'Create'},
    { id: 'subs', title: 'Your Substitutions', mobileTitle: 'Substitutions'},
];
let props = defineProps({
    screenProp: {
        type: String,
        default: 'default'
    }
});

let activeScreen = ref("apps");
if (props.screenProp !== "default") {
    activeScreen.value = props.screenProp;
}

let period = reactive({
    start: null,
    end: null,
});
const subpageClass = "rounded-bl-md rounded-br-md laptop:rounded-tr-md";
let isEditing = ref(false);
let applicationNo = ref(null);
async function handleEditApplication(appNo) {
    appNo = parseInt(appNo);
    applicationNo.value = appNo;

    for (let app of applications.value) {
        if (app.applicationNo == appNo) {
            period.start = app.sDate;
            period.end = app.eDate;
            break;
        }
    }

    await fetchNominationsForApplicationNo(appNo, user.value.accountNo);
    isEditing.value = true;
}

function changeUrl(params) {
    var baseUrl = window.location.origin;

    history.pushState(
        null,
        'LeaveOnTime',
        baseUrl + "/bookings/" + params
    );
}

function handleActiveScreenChanged(screen) {
    activeScreen.value = screen;

    changeUrl(screen);
}
</script>

<template>
<PageLayout>
    <AuthenticatedLayout>
        <div v-if="$isMobile()" class="flex flex-col screen-mobile mt-2 mx-2 drop-shadow-md">
            <SubpageNavbar
                class="h-[5%]"
                :options="options"
                :activeScreen="activeScreen"
                @screen-changed="screen => handleActiveScreenChanged(screen)"
            />
            <ApplicationsSubpage
                v-show="activeScreen === 'apps'" 
                :class="subpageClass"
                class="p-2 h-[95%]"
                @editApplication="(applicationNo) => handleEditApplication(applicationNo)"
            />
            <CreateSubpage
                class="h-[95%]"
                v-show="activeScreen === 'create'" 
                :subpageClass="subpageClass"
            />
            <SubstitutionsSubpage
                v-show="activeScreen === 'subs'" 
                :class="subpageClass"
                class="p-2 h-[95%]"
            />
        </div>
        <div v-else class="flex flex-col screen mt-4 mx-4 drop-shadow-md">
            <SubpageNavbar
                class="h-[5%]"
                :class="activeScreen === 'create' ? 'w-4/5 1080:w-[85%] 1440:w-5/6 pr-4 ': ''"
                :options="options"
                :activeScreen="activeScreen"
                @screen-changed="screen => handleActiveScreenChanged(screen)"
            />
            <ApplicationsSubpage
                v-show="activeScreen === 'apps'" 
                :class="subpageClass"
                class="p-4 h-[95%]"
                @editApplication="(applicationNo) => handleEditApplication(applicationNo)"
            />
            <CreateSubpage
                class="h-[95%]"
                v-show="activeScreen === 'create'" 
                :subpageClass="subpageClass"
            />
            <SubstitutionsSubpage
                v-show="activeScreen === 'subs'" 
                :class="subpageClass"
                class="p-4 h-[95%]"
            />
        </div>
    </AuthenticatedLayout>
    <EditApplication
        v-show="isEditing"
        :applicationNo="applicationNo"
        :subpageClass="subpageClass"
        :period="period"
        @close="isEditing = false; applicationNo = null;"
    />
</PageLayout>
</template>

<style>
.screen {
    height: calc(93vh - 3rem);
}
.screen-mobile {
    height: calc(93vh - 1.5rem);
}
</style>