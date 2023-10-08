<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SubpageNavbar from "@/Components/SubpageNavbar.vue";
import PageLayout from "@/Layouts/PageLayout.vue";
import AppRequestSubpage from '@/Components/Manager/AppRequestSubpage.vue';
import ManageStaffSubpage from '@/Components/Manager/ManageStaffSubpage.vue';
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
import { ref } from 'vue';

const options = [
    { id: 'appRequest', title: 'Applications', mobileTitle: 'Applications'},
    { id: 'manage', title: 'Manage Staff', mobileTitle: 'Manage Staff'},
];

let props = defineProps({
    screenProp: {
        type: String,
        default: 'default'
    }
});

let activeScreen = ref("appRequest");
if (props.screenProp !== "default") {
    activeScreen.value = props.screenProp;
}

const subpageClass = "rounded-bl-md rounded-br-md rounded-tr-md";

function changeUrl(params) {
    var baseUrl = window.location.origin;
    history.pushState(
        null,
        'LeaveOnTime',
        baseUrl + "/manager/" + params
    );
    window.location.reload();
}

function handleActiveScreenChanged(screen) {
    activeScreen.value = screen;

    changeUrl(screen);
}
</script>

<template>
    <PageLayout>
        <AuthenticatedLayout>
            <div v-if="isMobile" class="flex flex-col screen-mobile mt-2 mx-2 drop-shadow-md">
                <SubpageNavbar
                    class="h-[5%]"
                    :options="options"
                    :activeScreen="activeScreen"
                    @screen-changed="screen => handleActiveScreenChanged(screen)"
                />
                <AppRequestSubpage
                    class="p-2 h-[95%]"
                    v-show="activeScreen === 'appRequest'" 
                    :class="subpageClass"
                />
                <ManageStaffSubpage
                    class="p-2 h-[95%]"
                    v-show="activeScreen === 'manage'" 
                    :class="subpageClass"
                />
            </div>
            <div v-else class="flex flex-col screen mt-4 mx-4 drop-shadow-md">
                <SubpageNavbar
                    class="h-[5%]"
                    :options="options"
                    :activeScreen="activeScreen"
                    @screen-changed="screen => handleActiveScreenChanged(screen)"
                />
                <AppRequestSubpage
                    class="p-4 h-[95%]"
                    v-show="activeScreen === 'appRequest'" 
                    :class="subpageClass"
                />
                <ManageStaffSubpage
                    class="p-4 h-[95%]"
                    v-show="activeScreen === 'manage'" 
                    :class="subpageClass"
                />
            </div>
        </AuthenticatedLayout>
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
