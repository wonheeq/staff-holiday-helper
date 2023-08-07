<script setup>
import ApplicationInfo from "@/Components/Bookings/ApplicationInfo.vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { onMounted } from 'vue';
import { useCalendarStore } from '@/stores/CalendarStore';
let calendarStore = useCalendarStore();
const { fetchCalendarData } = calendarStore;
let applicationStore = useApplicationStore();
const { applications } = storeToRefs(applicationStore);
const { fetchApplications } = applicationStore;


onMounted(() => {
    fetchApplications();
});

let deadAreaColor = "#FFFFFF";
</script>
<template>
    <VueScrollingTable
        :deadAreaColor="deadAreaColor"
        :scrollHorizontal="false"
    >
        <template #tbody>
            <div v-for="item in applications" :key="item.id" class="mb-2">
                <ApplicationInfo
                    :source="item"
                    @cancelApplication="item.status = 'C'; fetchCalendarData()"
                ></ApplicationInfo>
            </div>
        </template>
    </VueScrollingTable>
</template>