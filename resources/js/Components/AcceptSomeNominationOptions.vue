<script setup>
import { ref } from 'vue';
import { useDark } from "@vueuse/core";
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
// const { $isMobile() } = storeToRefs(screenSizeStore);
const isDark = useDark();

let status = ref('U');
let emit = defineEmits(['statusUpdated']);

function buttonClass(isAccept) {
    let c = "";
    if (isDark.value) {
        c = c + 'text-white';

        if (status.value === 'Y' && isAccept) {
            c = c + ' bg-green-800';
            return c;
        }
        else if(status.value ==='N' && !isAccept) {
            c = c + ' bg-red-800';
            return c;
        }
    }
    else {
        if (status.value === 'Y' && isAccept) {
            c = c + ' bg-green-500';
            return c;
        }
        else if(status.value ==='N' && !isAccept) {
            c = c + ' bg-red-500';
            return c;
        }
    }

    return c;
}
</script>
<template>
    <div v-if="$isMobile()" class="space-y-2">
        <button class="rounded-md border w-full border-black p-1 px-2"
            :class="buttonClass(true)"
            @click="status = 'Y'; emit('statusUpdated', status)"
        >
            Accept
        </button>
        <button class="rounded-md border w-full border-black p-1 px-2"
            :class="buttonClass(false)"
            @click="status = 'N'; emit('statusUpdated', status)"
        >
            Reject
        </button>
    </div>
    <div v-else class="flex space-x-4">
        <button class="rounded-md border border-black p-2 px-6 4k:text-2xl"
            :class="buttonClass(true)"
            @click="status = 'Y'; emit('statusUpdated', status)"
        >
            Accept
        </button>
        <button class="rounded-md border border-black p-2 px-6 4k:text-2xl"
            :class="buttonClass(false)"
            @click="status = 'N'; emit('statusUpdated', status)"
        >
            Reject
        </button>
    </div>
</template>