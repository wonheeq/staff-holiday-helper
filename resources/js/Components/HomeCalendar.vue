<script setup>
import { Calendar, DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import { ref } from 'vue';

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
<div class="bg-white rounded-md">
    <p class="text-4xl font-bold text-center m-4 mt-8 mb-8">Your Itinerary</p>
    <Calendar
        :rows="3"
        borderless
        expanded
        transparent
        :attributes="attributes"
    />
    <div class="px-6">
        <p class="text-2xl font-bold">Legend</p>
        <div class="flex flex-row my-2">
            <div class="bg-green-400 dot mr-2"></div>
            <p class="">Approved Booking</p>
        </div>
        <div class="flex flex-row mb-2">
            <div class="bg-orange-400 dot mr-2"></div>
            <p class="">Pending Booking</p>
        </div>
        <div class="flex flex-row mb-2">
            <div class="bg-red-400 dot mr-2"></div>
            <p class="">Rejected Booking</p>
        </div>
        <div class="flex flex-row mb-2">
            <div class="bg-purple-400 dot mr-2"></div>
            <p class="">Substitutions</p>
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