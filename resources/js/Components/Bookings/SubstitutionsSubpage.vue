<script setup>
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { useSubstitutionStore } from '@/stores/SubstitutionStore';
import { storeToRefs } from 'pinia';
const substitutionStore = useSubstitutionStore();
const { substitutions } = storeToRefs(substitutionStore);
let deadAreaColor = "#FFFFFF";
</script>
<template>
    <div class="subpage-height w-full">
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