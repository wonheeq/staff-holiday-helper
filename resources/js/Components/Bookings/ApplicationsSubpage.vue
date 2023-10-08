<script setup>
import ApplicationInfo from "@/Components/Bookings/ApplicationInfo.vue";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { computed } from 'vue';
import { useCalendarStore } from '@/stores/CalendarStore';
import { usePage } from '@inertiajs/vue3'
import { useDark } from "@vueuse/core";
const isDark = useDark();
const page = usePage();
const user = computed(() => page.props.auth.user);
let calendarStore = useCalendarStore();
const { fetchCalendarData } = calendarStore;
let applicationStore = useApplicationStore();
const { applications } = storeToRefs(applicationStore);

let emit = defineEmits(["editApplication"]);

let deadAreaColor = computed(() => {
    return isDark.value ? '#1f2937': '#FFFFFF';
});
</script>
<template>
    <VueScrollingTable
        :deadAreaColor="deadAreaColor"
        :scrollHorizontal="false"
        :class="isDark?'scrollbar-dark':''"
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