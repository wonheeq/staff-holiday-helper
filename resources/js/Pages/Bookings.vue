<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SubpageNavbar from "@/Components/SubpageNavbar.vue";
import ApplicationsSubpage from '@/Components/Bookings/ApplicationsSubpage.vue';
import CreateSubpage from '@/Components/Bookings/CreateSubpage.vue';
import SubstitutionsSubpage from '@/Components/Bookings/SubstitutionsSubpage.vue';
import EditApplication from "@/Components/Bookings/EditApplication.vue";
import { ref } from 'vue';
import { useNominationStore } from '@/stores/NominationStore';
let nominationStore = useNominationStore();
const { fetchNominationsForApplicationNo } = nominationStore;
const options = [
    { id: 'apps', title: 'Applications'},
    { id: 'create', title: 'Create New Application'},
    { id: 'subs', title: 'Your Substitutions'},
];
let props = defineProps({
    activeScreen: {
        type: String,
        default: 'apps',
    }
});
const subpageClass = "rounded-bl-md rounded-br-md rounded-tr-md bg-white";
let isEditing = ref(false);
let applicationNo = ref(null);
async function handleEditApplication(appNo) {
    appNo = parseInt(appNo);
    isEditing.value = true;
    applicationNo.value = appNo;

    await fetchNominationsForApplicationNo(appNo);
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex flex-col screen mt-4 mx-4 drop-shadow-md">
            <SubpageNavbar
                class="h-[5%]"
                :options="options"
                :activeScreen="activeScreen"
                @screen-changed="screen => activeScreen = screen"
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
            <Teleport to="body">
                <EditApplication
                    v-show="isEditing"
                    :applicationNo="applicationNo"
                    :subpageClass="subpageClass"
                    @close="isEditing = false; applicationNo = false;"
                />
            </Teleport>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.screen {
    height: calc(93vh - 3rem);
}
</style>