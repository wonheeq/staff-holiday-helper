<script setup>
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import{ inject } from 'vue';
const dayJS = inject("dayJS");
let props = defineProps({
    period: Object,
    isEditing: {
        type: Boolean,
        default: false,
    },
});

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
<div class="h-[90%]">
	<div class="flex flex-col w-fit h-full">
		<div>
			<div class="mb-2 laptop:mb-8">
				<p class="laptop:text-lg 1080:text-2xl 1440:text-4xl 4k:text-5xl"> Start date and time: </p>
				<div class="flex mt-2 justify-between">
					<VueDatePicker v-model="period.start" time-picker-inline :format="'dd/MM/yyyy HH:mm'" auto-apply :clearable="false"/>
				</div>
			</div>
			<div class="mb-4 laptop:mb-8">
				<p class="laptop:text-lg 1080:text-2xl 1440:text-4xl 4k:text-5xl"> End date and time: </p>
				<div class="flex mt-2 justify-between">
					<VueDatePicker v-model="period.end" time-picker-inline :format="'dd/MM/yyyy HH:mm'" auto-apply :clearable="false" />
				</div>
			</div>
		</div>
		<div class="mb-4 laptop:mt-auto laptop:mb-12">
			<p class="laptop:text-lg 1080:text-2xl 1440:text-4xl 4k:text-5xl"> Application Details: </p>
			<div class="mt-1 laptop:mt-4">
				<span class="flex w-full">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl" > Start Date: </p>
					<p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ formatDate(period.start) }}
					</p>
				</span>
				<span class="flex w-full">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"> Start Time:</p>
					<p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ formatTime(period.start) }}
					</p>
				</span>
				<span class="flex mt-4">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"> End Date:</p>
					<p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ formatDate(period.end) }}
					</p>
				</span>
				<span class="flex w-full">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"> End Time:</p>
					<p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ formatTime(period.end) }}
					</p>
				</span>
				<span class="flex mt-4 ">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"> Duration:</p>
					<p :class="{
                        'text-red-600': dayJS(period.end).diff(dayJS(period.start)) <= 0
                    }" class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ calcDuration(period) }}
					</p>
				</span>
			</div>
		</div>
	</div>
</div>
</template>