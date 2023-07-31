<script setup>
import{ inject } from 'vue';
const dayJS = inject("dayJS");
let props = defineProps({ period: Object });

let formatDate = (date) => {
    if (date !== null) {
        return dayJS(date).format('dddd, DD MMM YYYY');
    }
    return "";
};

let formatTime = (date) => {
    if (date !== null) {
        return dayJS(date).format('hh:mm A');
    }
    return "";
};

let calcDuration = (dates) => {
    if (dates !== null) {
        if (dates.start !== null && dates.end !== null) {
            let start = dayJS(dates.start);
            let end = dayJS(dates.end);
            let rawHours = end.diff(start, "hour", true);
            let days = Math.floor(rawHours / 24);
            let hoursRemaining = rawHours % 24;
            let hours = Math.floor(hoursRemaining);
            let minutes = Math.floor((hoursRemaining % 1) * 60);
            return days + " days, " + hours + " hours " + minutes + " minutes";
        }
    }

    return "NaN";
};
</script>
<template>
    <div>
        <div class="flex flex-col w-fit h-full">
            <div class="mb-8">
                <p class="text-4xl">
                    Enter start date and time:
                </p>
                <div class="flex mt-2 justify-between">
                    <input type="datetime-local" v-model="period.start" />
                </div>
            </div>
            <div class="mb-8">
                <p class="text-4xl">
                    Enter end date and time:
                </p>
                <div class="flex mt-2 justify-between">
                    <input type="datetime-local" v-model="period.end" />
                </div>
            </div>
            <div class="mt-auto mb-12">
        <p class="text-4xl">
            Application Details:
        </p>
        <div class="mt-4">
            <span class="flex">
                <p class="w-32 font-bold">
                    Start Date:
                </p>
                <p>
                    {{ formatDate(period.start) }}
                </p>
            </span>
            <span class="flex">
                <p class="w-32 font-bold">
                    Start Time:
                </p>
                <p>
                    {{ formatTime(period.start) }}
                </p>
            </span>
            <span class="flex mt-4">
                <p class="w-32 font-bold">
                    End Date:
                </p>
                <p>
                    {{ formatDate(period.end) }}
                </p>
            </span>
            <span class="flex">
                <p class="w-32 font-bold">
                    End Time:
                </p>
                <p>
                    {{ formatTime(period.end) }}
                </p>
            </span>
            <span class="flex mt-4">
                <p class="w-32 font-bold">
                    Duration:
                </p>
                <p :class="{
                    'text-red-600': dayJS(period.end).diff(dayJS(period.start)) <= 0
                }">
                    {{ calcDuration(period) }}
                </p>
            </span>
        </div>
    </div>
        </div>
    </div>
</template>