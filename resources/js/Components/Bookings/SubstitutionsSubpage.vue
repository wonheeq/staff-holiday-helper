<script setup>
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { useSubstitutionStore } from '@/stores/SubstitutionStore';
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const substitutionStore = useSubstitutionStore();
const { substitutions } = storeToRefs(substitutionStore);
let deadAreaColor = "#FFFFFF";
</script>
<template>
    <div v-if="isMobile" class="subpage-height-mobile laptop:subpage-height w-full mb-2">
        <div class="h-[5%]">
            <p class="font-bold text-3xl laptop:text-5xl">
                Your Substitutions
            </p>
            <p class="laptop:pt-4 laptop:text-2xl">
                You have agreed to substitute for the following:
            </p>
        </div>
        <div class="h-[95%] border-black border">
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
                            <p class="text-lg laptop:text-xl">
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
        <p class="text-transparent h-2 text-sm">e</p>
    </div>
    <div v-else class="subpage-height w-full">
        <div class="h-[15%] 1080:h-[12%] 1440:h-[9%] 4k:h-[6%]">
            <p class="font-bold laptop:text-3xl 1080:text-5xl">
                Your Substitutions
            </p>
            <p class="laptop:pt-4 laptop:text-xl 1080:text-2xl">
                You have agreed to substitute for the following:
            </p>
        </div>
        <div class="h-[85%] 1080:h-[88%] 1440:h-[91%] 4k:h-[94%] border-black border">
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
                            <p class="text-lg laptop:text-xl">
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
.subpage-height-mobile {
    /* 95% of (screen - 7vh for navbar height - 1.5rem for 3x gaps ) + ((screen - 7vh for navbar height - 1.5rem for 3x gaps ) - 2rem from the subpagenavbar height of h-8 css)   */
    height: calc(0.95 * (93vh - 1.5rem + ((0.95 * (93vh - 1.5rem)) - 2rem )));
}
</style>