<script setup>
import Shortcut from './Shortcut.vue';
import Swal from 'sweetalert2';
import { inject } from 'vue';
import { storeToRefs } from 'pinia';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { useDark } from "@vueuse/core";
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const isDark = useDark();
const dayJS = inject("dayJS");
let applicationStore = useApplicationStore();
const { applications } = storeToRefs(applicationStore);
import { useSubstitutionStore } from '@/stores/SubstitutionStore';
const substitutionStore = useSubstitutionStore();
const { substitutions } = storeToRefs(substitutionStore);
let props = defineProps({ welcomeData: Object });

let copyEmail = () => { 
    let email = props.welcomeData.lineManager.id + "@curtin.edu.au";
    navigator.clipboard.writeText(email); 
    Swal.fire({
        'title':"Email address copied to clipboard.",
        'icon':'info'
    });   
};

function formatDate(date) {
    if (date !== null) {
        return dayJS(date).format('ddd, DD MMM YYYY');
    }
    return "";
}
</script>

<template>
    <div class="flex flex-col items-center" v-if="props.welcomeData">
        <div class="flex flex-row w-full">
            <div class="flex flex-col w-full items-center" :class="isMobile ? isDark ? 'bg-gray-800 rounded-md py-2 mb-1 drop-shadow-md':'bg-white rounded-md py-2 mb-1 drop-shadow-md':''">
                <p class="text-sm laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">
                    Welcome {{ props.welcomeData.name }},
                </p>
                <p  class="text-xs laptop:text-base 1080:text-xl 1440:text-2xl 4k:text-4xl">
                    Your line manager is currently {{ props.welcomeData['lineManager']['name'] }}
                    <input @click="copyEmail"
                        type="image"
                        class="h-5 1440:h-8 4k:h-14 align-middle"
                        :class="isDark?'darkModeEmail':''"
                        src="/images/mail.svg"
                        title="Copy Email Address to Clipboard"
                        v-title
                    />
                </p>
            </div>
        </div>
        <div class="grid grid-cols-3 rounded-md p-2 laptop:p-4 1440:p-6 laptop:w-4/5 mt-1 laptop:mt-2 h-full drop-shadow-md text-black"
            :class="isDark ? 'bg-gray-800': 'bg-white'"
        >
            <Shortcut class="bg-green-200" href="/bookings/apps">
                Your Leave Applications
                <template #content>
                    <ul class="text-left text-xs 1080:text-base 1440:text-lg 4k:text-4xl text-black">
                        <li>{{ applications.filter(app => app.status == 'Y').length }} Approved</li>
                        <li>{{ applications.filter(app => app.status == 'U').length }} Undecided</li>
                        <li>{{ applications.filter(app => app.status == 'P').length }} Pending</li>
                        <li>{{ applications.filter(app => app.status == 'N').length }} Denied</li>
                    </ul>
                </template>
                <template #strip>
                    <div class="h-2 1440:h-3 mt-auto mb-6 1440:mb-12 bg-green-400"></div>
                </template>
            </Shortcut>
            <Shortcut class="bg-orange-200" href="/bookings/create">
                <template #content>
                    <p class="laptop:mt-4 text-sm 1080:text-xl 1440:text-2xl 4k:text-4xl font-bold text-black">Create New Leave Application</p>
                </template>
                <template #strip>
                    <div class="h-2 1440:h-3 mt-auto mb-6 1440:mb-12 bg-orange-400"></div>
                </template>
            </Shortcut>
            <Shortcut class="bg-purple-200" href="/bookings/subs">
                Your Substitutions
                <template #content>
                    <p class="px-2 text-xs 1080:text-base 1440:text-lg 4k:text-4xl">
                        {{ substitutions.length }} upcoming substitutions.
                    </p>
                    <p v-if="substitutions.length" class="px-2 1440:mt-4 mt-2 4k:mt-8 text-xs 1080:text-base 1440:text-lg 4k:text-4xl">
                        <!--Assume that the first element is the earliest date-->
                        Next: {{ formatDate(new Date(substitutions[0]['sDate'])) }}
                    </p>
                </template>
                <template #strip>
                    <div class="h-2 1440:h-3 mt-auto mb-6 1440:mb-12 bg-purple-400"></div>
                </template>
            </Shortcut>
        </div>
    </div>
</template>
<style>
.darkModeEmail {
    filter: invert(0%) sepia(0%) saturate(100%) hue-rotate(0deg) brightness(200%) contrast(60%);
}
</style>