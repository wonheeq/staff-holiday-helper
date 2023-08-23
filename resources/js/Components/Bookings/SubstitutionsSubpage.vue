<script setup>
import { onMounted, ref, computed } from 'vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { usePage } from '@inertiajs/vue3'
const page = usePage();
const user = computed(() => page.props.auth.user);
let deadAreaColor = "#FFFFFF";

let substitutions = [];

let fetchSubstitutions = async() => {
    try {
        const resp = await axios.get('/api/getSubstitutionsForUser/' + user.value.accountNo);
        substitutions = resp.data;
    } catch (error) {
        alert("Failed to load data: Please try again");
        console.log(error);
    }
}; 

const dataReady = ref(false);

onMounted(async () => {
    await fetchSubstitutions();
    dataReady.value = true;
});
</script>
<template>
    <div v-if="dataReady" class="subpage-height w-full">
        <div class="h-[10%]">
            <p class="font-bold text-5xl">
                Your Substitutions
            </p>
            <p class="pt-4 text-2xl">
                You have agreed to substitute for the following:
            </p>
        </div>
        <div class="h-[90%] border-black border">
            <VueScrollingTable
                class=""
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div v-for="item in substitutions" :key="item.id"
                        class=" bg-gray-200 border-b-8 border-white"
                    >
                        <div class="px-2 py-2">
                            <p class="text-xl">
                                {{ item.task }} for {{  item.applicantName }}
                            </p>
                            <p>
                                {{ item.sDate }} - {{ item.eDate }}
                            </p>
                        </div>
                    </div>
                </template>
            </VueScrollingTable>
        </div>
    </div>
</template>

<style>
.subpage-height {
    height: calc(0.95 * (93vh - 3rem));
}
</style>