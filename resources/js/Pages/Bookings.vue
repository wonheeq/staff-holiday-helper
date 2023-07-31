<script setup>
import LoggedInView from "@/Components/LoggedInView.vue";
import SubpageNavbar from "@/Components/SubpageNavbar.vue";
import ApplicationsSubpage from '@/Components/Bookings/ApplicationsSubpage.vue';
import CreateSubpage from '@/Components/Bookings/CreateSubpage.vue';
import SubstitutionsSubpage from '@/Components/Bookings/SubstitutionsSubpage.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const options = [
    { id: 'applications', title: 'Applications'},
    { id: 'create', title: 'Create New Application'},
    { id: 'subs', title: 'Your Substitutions'},
];
let activeScreen = ref("applications");
const subpageClass = "p-4 rounded-bl-md rounded-br-md rounded-tr-md bg-white h-[95%]";
</script>

<template>
    <LoggedInView>
        <div class="flex flex-col screen mt-4 mx-4 drop-shadow-md">
            <SubpageNavbar
                class="h-[5%]"
                :options="options"
                @screen-changed="screen => activeScreen = screen"
            />
            <ApplicationsSubpage
                v-show="activeScreen === 'applications'" 
                :class="subpageClass"
            />
            <CreateSubpage
                v-show="activeScreen === 'create'" 
                :class="subpageClass"
            />
            <SubstitutionsSubpage
                v-show="activeScreen === 'subs'" 
                :class="subpageClass"
            />
        </div>
    </LoggedInView>
</template>

<style>
.screen {
    height: calc(93vh - 3rem);
}
</style>