<script setup>
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';
import { ref, onMounted, computed } from 'vue';
import { useResizeObserver } from 'vue-screen-utils';
import { useCalendarStore } from '@/stores/CalendarStore';
import { storeToRefs } from 'pinia';
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const user = computed(() => page.props.auth.user);
let calendarStore = useCalendarStore();
const { calendarData } = storeToRefs(calendarStore);
const { fetchCalendarData } = calendarStore;

onMounted(() => {
    fetchCalendarData(user.value.accountNo);
})

let emit = defineEmits(['enlarge-calendar']);

let props = defineProps({ disableEnlarge: Boolean });
const divRef = ref(null);
const { rect } = useResizeObserver(divRef);
const rows = computed(() => {
    return Math.max(Math.floor((rect.value?.height - 250)/ 290), 1) || 1;
});
</script>

<template>
<div ref="divRef" class="bg-white rounded-md flex flex-col">
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
        :attributes="calendarData"
    >
    </Calendar>
    <div class="px-2 mt-auto laptop:mb-4">
        <p class="text-lg 1080:text-xl 1440:text-3xl 4k:text-4xl font-bold">Legend:</p>
        <div class="grid grid-cols-2">
            <div>
                <div class="flex flex-row my-2 items-center">
                    <div class="bg-green-400 dot mr-2"></div>
                    <p class="text-sm 1080:text-base 4k:text-2xl">Approved Booking</p>
                </div>
                <div class="flex flex-row mb-2 items-center">
                    <div class="bg-red-400 dot mr-2"></div>
                    <p class="text-sm 1080:text-base  4k:text-2xl">Rejected Booking</p>
                </div>
                <div class="flex flex-row mb-2 items-center">
                    <div class="bg-blue-400 dot mr-2"></div>
                    <p class="text-sm 1080:text-base 4k:text-2xl">Undecided Booking</p>
                </div>
            </div>
            <div>
                <div class="flex flex-row my-2 items-center">
                    <div class="bg-purple-400 dot mx-2"></div>
                    <p class="text-sm 1080:text-base 4k:text-2xl">Substitutions</p>
                </div>
                <div class="flex flex-row mb-2 items-center">
                    <div class="bg-orange-400 dot mx-2"></div>
                    <p class="text-sm 1080:text-base 4k:text-2xl">Pending Booking</p>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style>
.dot {
  height: 16px;
  width: 16px;
  border-radius: 50%;
  display: inline-block;
}

.enlarge {
    height: 26px;
    width: auto;
}

/* laptop */
@media 
(min-width: 1360px) {
    .enlarge {
        height: 30px;
    }
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