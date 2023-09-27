<script setup>
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

let formatTime = (time) => {
    if (time !== null) {
        return time;
    }
    return "";
};

let calcDuration = (dates) => {
    if (dates !== null) {
        if (dates.start.date !== null && dates.end.date !== null && dates.start.time !== null && dates.end.time !== null) {
            let start = dayJS(dates.start.date + " " + dates.start.time);
            let end = dayJS(dates.end.date + " " + dates.end.time);
            let rawHours = end.diff(start, "hour", true);
            let days = Math.floor(Math.abs(rawHours) / 24);
            let hoursRemaining = Math.abs(rawHours) % 24;
            let hours = Math.floor(hoursRemaining);
            let minutes = Math.floor((hoursRemaining % 1) * 60);
            let duration = days + " days, " + hours + " hours " + minutes + " minutes";

			if (rawHours < 0) {
				duration = "-" + duration;
			}
			return duration;
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
				<div class="flex mt-2 space-x-4">
					<input type="date" v-model="period.start.date" class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"/>
					<input type="time" v-model="period.start.time" class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"/>
				</div>
			</div>
			<div class="mb-4 laptop:mb-8">
				<p class="laptop:text-lg 1080:text-2xl 1440:text-4xl 4k:text-5xl"> End date and time: </p>
				<div class="flex mt-2 space-x-4">
					<input type="date" v-model="period.end.date" class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl" />
					<input type="time" v-model="period.end.time" class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"/>
				</div>
			</div>
		</div>
		<div class="mb-4 laptop:mt-auto laptop:mb-12">
			<p class="laptop:text-lg 1080:text-2xl 1440:text-4xl 4k:text-5xl"> Application Details: </p>
			<div class="mt-1 laptop:mt-4">
				<span class="flex w-full">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl" > Start Date: </p>
					<p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ formatDate(period.start.date) }}
					</p>
				</span>
				<span class="flex w-full">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"> Start Time:</p>
					<p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ formatTime(period.start.time) }}
					</p>
				</span>
				<span class="flex mt-4">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"> End Date:</p>
					<p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ formatDate(period.end.date) }}
					</p>
				</span>
				<span class="flex w-full">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"> End Time:</p>
					<p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ formatTime(period.end.time) }}
					</p>
				</span>
				<span class="flex mt-4 ">
					<p class="w-20 laptop:w-32 font-bold text-xs 1080:text-lg 1440:text-xl 4k:text-2xl"> Duration:</p>
					<p :class="{
                        'text-red-600': dayJS(period.end.date + ' ' + period.end.time).diff(dayJS(period.start.date + ' ' + period.start.time)) <= 0
                    }" class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl">
						{{ calcDuration(period) }}
					</p>
				</span>
			</div>
		</div>
	</div>
</div>
</template>