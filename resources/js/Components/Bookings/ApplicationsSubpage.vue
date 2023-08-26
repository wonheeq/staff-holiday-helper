<script setup>
import ApplicationInfo from "@/Components/Bookings/ApplicationInfo.vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { onMounted, computed } from 'vue';
import { useCalendarStore } from '@/stores/CalendarStore';
import { usePage } from '@inertiajs/vue3'
const page = usePage();
const user = computed(() => page.props.auth.user);
let calendarStore = useCalendarStore();
const { fetchCalendarData } = calendarStore;
let applicationStore = useApplicationStore();
const { applications } = storeToRefs(applicationStore);
const { fetchApplications } = applicationStore;

let emit = defineEmits(["editApplication"]);
onMounted(() => {
    fetchApplications(user.value.accountNo);
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
                    @cancelApplication="item.status = 'C'; fetchCalendarData(user.accountNo)"
                    @editApplication="$emit('editApplication', item.applicationNo)"
                ></ApplicationInfo>
            </div>
        </template>
    </VueScrollingTable>
</template>