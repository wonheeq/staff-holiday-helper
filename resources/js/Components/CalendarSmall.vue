<script setup>
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';
import { ref } from 'vue';
import { useScreens } from 'vue-screen-utils';
let emit = defineEmits(['enlarge-calendar']);

let props = defineProps({ disableEnlarge: Boolean });
const { mapCurrent } = useScreens({
    'laptop': '760px',
    '1080p': '1920px',
    '1440p': '2560px',
    '4k': '3840px',
});
const rows = mapCurrent({ '4k': 5, '1440p': 3, '1080p': 2 }, 1);

const day = 86400000;
let attributes = ref([
    {
        key: 'today',
        bar: true,
        dates: new Date(),
    },
    {
        highlight: 'green',
        dates: [
            [Date.now() + 1 * day, new Date(Date.now() + 5 * day)]
        ],
        popover: {
            label: 'Approved (XX/XX/2023 12:00 AM - XX/XX/2023 12:00 AM)'
        },
    },
    {
        highlight: 'red',
        dates: [
            [Date.now() + 38 * day, new Date(Date.now() + 39 * day)]
        ],
        popover: {
            label: 'Rejected by System: A nominee has declined to takeover a responsibility.'
        },
    },
    {
        highlight: 'red',
        dates: [
            [Date.now() + 13 * day, new Date(Date.now() + 15 * day)]
        ],
        popover: {
            label: 'Rejected by Line Manager: Not enough leave remaining.'
        },
    },
    {
        highlight: 'orange',
        dates: [
            [Date.now() + 27 * day, new Date(Date.now() + 31 * day)]
        ],
    },
    {
        highlight: 'purple',
        dates: [
            [Date.now() + 40 * day, new Date(Date.now() + 43 * day)]
        ],
        popover: {
            label: 'COMP2007: Something something (XX/XX/2023 12:00 AM - XX/XX/2023 12:00 AM)'
        },
    },
]);
</script>

<template>
<div class="bg-white rounded-md flex flex-col">
    <div class="flex mx-4 mt-4 items-center">
        <button class="absolute" v-show="!disableEnlarge">
            <img src="/images/fullscreen.svg"
                class="enlarge"
                @click="$emit('enlarge-calendar')"
            />
        </button>
        <p class="text-xl 1080:text-2xl 1440:text-3xl 4k:text-4xl font-bold mx-auto">
            Your Itinerary
        </p>
    </div>
    <Calendar
        :rows="rows"
        borderless
        expanded
        transparent
        :attributes="attributes"
        trim-weeks
    >
    </Calendar>
    <div class="px-6 mt-auto mb-4">
        <p class="text-lg 1080:text-xl 1440:text-3xl 4k:text-4xl font-bold">Legend</p>
        <div class="flex flex-row my-2 items-center">
            <div class="bg-green-400 dot mr-2"></div>
            <p class="text-sm 1080:text-base 1440:text-xl 4k:text-2xl">Approved Booking</p>
        </div>
        <div class="flex flex-row mb-2 items-center">
            <div class="bg-orange-400 dot mr-2"></div>
            <p class="text-sm 1080:text-base 1440:text-xl 4k:text-2xl">Pending Booking</p>
        </div>
        <div class="flex flex-row mb-2 items-center">
            <div class="bg-red-400 dot mr-2"></div>
            <p class="text-sm 1080:text-base 1440:text-xl 4k:text-2xl">Rejected Booking</p>
        </div>
        <div class="flex flex-row items-center">
            <div class="bg-purple-400 dot mr-2"></div>
            <p class="text-sm 1080:text-base 1440:text-xl 4k:text-2xl">Substitutions</p>
        </div>
    </div>
</div>
</template>

<style>
.dot {
  height: 25px;
  width: 25px;
  border-radius: 50%;
  display: inline-block;
}

.enlarge {
    height: 30px;
    width: auto;
}

/* 1080p */
@media 
(min-width: 1920px) {
   .enlarge {
        height: 36px;
    }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .enlarge {
        height: 40px;
    }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .enlarge {
        height: 50px;
        width: auto;
    }
}
</style>