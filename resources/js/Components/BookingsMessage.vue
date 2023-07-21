<script setup>
let props = defineProps({ source: Object });

const statusText = {
    "P": "Pending",
    "U": "Undecided",
    "Y": "Approved",
    "N": "Denied",
};
const statusColour = {
    "P": "text-yellow-500",
    "U": "text-yellow-500",
    "Y": "text-green-500",
    "N": "text-red-500",
};
</script>
<template>
    <div class="flex flex-row bg-gray-200 mr-4">
        <div class="flex flex-col w-5/6 p-2">
            <p class="text-xl font-bold">{{ source.start }} - {{ source.end }}</p>
            <div class="flex flex-row">
                <p class="font-medium mr-2">Application ID:</p>
                {{ source.id }}
            </div>
            <div>
                <p class="font-medium">Substitute/s:</p>
                <p v-for="nomination in source.nominations">
                    → {{ nomination.name }} ({{ nomination.user_id }}) - [{{ nomination.user_id }}@curtin.edu.au]    {{ nomination.task }}
                </p>
            </div>
            <div class="flex flex-row">
                <p class="font-medium mr-2">Application Submitted:</p>
                {{ source.created_at }}
            </div>
        </div>
        <div class="flex flex-col w-1/6 p-2 text-4xl"
            :class="statusColour[source.status]"
        >
            {{ statusText[source.status] }}
        </div>
        <div>
            <button class="text-5xl p-2">
                ^
            </button>
        </div>
    </div>
</template>