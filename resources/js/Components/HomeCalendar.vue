<script setup>
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';
import { ref } from 'vue';
import { useScreens } from 'vue-screen-utils';

const { mapCurrent } = useScreens({
    'laptop': '710px',
    '1080p': '1290px',
    '1440p': '1930px',
});
const rows = mapCurrent({ '1440p': 3, '1080p': 2 }, 1);

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
    },
    {
        highlight: 'red',
        dates: [
            [Date.now() + 38 * day, new Date(Date.now() + 39 * day)],
            [Date.now() + 13 * day, new Date(Date.now() + 15 * day)]
        ],
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
    },
]);
</script>

<template>
<div class="bg-white rounded-md flex flex-col">
    <p class="text-2xl 1440:text-4xl font-bold text-center mx-4 mt-6">Your Itinerary</p>
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
        <p class="text-lg 1440:text-2xl font-bold">Legend</p>
        <div class="flex flex-row my-2 items-center">
            <div class="bg-green-400 dot mr-2"></div>
            <p class="text-sm 1440:text-md">Approved Booking</p>
        </div>
        <div class="flex flex-row mb-2 items-center">
            <div class="bg-orange-400 dot mr-2"></div>
            <p class="text-sm 1440:text-md">Pending Booking</p>
        </div>
        <div class="flex flex-row mb-2 items-center">
            <div class="bg-red-400 dot mr-2"></div>
            <p class="text-sm 1440:text-md">Rejected Booking</p>
        </div>
        <div class="flex flex-row items-center">
            <div class="bg-purple-400 dot mr-2"></div>
            <p class="text-sm 1440:text-md">Substitutions</p>
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
</style>