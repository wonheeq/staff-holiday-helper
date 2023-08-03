<script setup>
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';
import { ref, onMounted } from 'vue';
import { useScreens } from 'vue-screen-utils';
import { useCalendarStore } from '@/stores/CalendarStore';
import { storeToRefs } from 'pinia';
let calendarStore = useCalendarStore();
const { calendarData } = storeToRefs(calendarStore);
const { fetchCalendarData } = calendarStore;

onMounted(() => {
    fetchCalendarData();
});


let emit = defineEmits(['shrink-calendar']);

const { mapCurrent } = useScreens({
    'laptop': '760px',
    '1080p': '1920px',
    '1440p': '2560px',
    '4k': '3840px',
});
const rows = mapCurrent({ '4k': 6, '1440p': 4, '1080p': 3 }, 2);
</script>
<template>
    <div class="bg-white rounded-md flex flex-col">
        <div class="flex mx-4 mt-2 1440:mt-4 items-center">
            <button class="absolute">
                <img src="/images/fullscreen-exit.svg"
                    class="enlarge"
                    @click="emit('shrink-calendar')"
                />
            </button>
            <p class="text-xl 1080:text-2xl 1440:text-3xl 4k:text-4xl font-bold mx-auto">
                Your Itinerary
            </p>
        </div>
        <Calendar
            :rows="rows"
            :columns="4"
            borderless
            expanded
            transparent
            :attributes="calendarData"
        >
        </Calendar>
        <div class="absolute flex items-center bottom-2 1440:bottom-4 px-4 space-x-4">
            <p class="text-lg 1080:text-xl 1440:text-3xl 4k:text-4xl font-bold">Legend:</p>
            <div class="flex flex-row items-center">
                <div class="bg-green-400 dot mr-2"></div>
                <p class="text-sm 1080:text-base 1440:text-xl 4k:text-2xl">Approved Booking</p>
            </div>
            <div class="flex flex-row items-center">
                <div class="bg-orange-400 dot mr-2"></div>
                <p class="text-sm 1080:text-base 1440:text-xl 4k:text-2xl">Pending Booking</p>
            </div>
            <div class="flex flex-row items-center">
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