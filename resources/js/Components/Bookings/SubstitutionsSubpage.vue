<script setup>
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { useSubstitutionStore } from '@/stores/SubstitutionStore';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { useDark } from "@vueuse/core";
const isDark = useDark();
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const substitutionStore = useSubstitutionStore();
const { substitutions } = storeToRefs(substitutionStore);
const deadAreaColor = computed(() => {
    return isDark.value ? '#1f2937': '#FFFFFF';
});
</script>
<template>
    <div v-if="isMobile" class="subpage-height-mobile laptop:subpage-height w-full" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="h-[11%]">
            <p class="font-bold text-3xl laptop:text-5xl">
                Your Substitutions
            </p>
            <p class="laptop:pt-4 laptop:text-2xl">
                You have agreed to substitute for the following:
            </p>
        </div>
        <div class="h-[89%] border" :class="isDark?'border-gray-400':'border-black'">
            <VueScrollingTable
                class=""
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
                :class="isDark?'scrollbar-dark':''"
            >
                <template #tbody>
                    <div v-for="item in substitutions" :key="item.id"
                    class="border-b-8"
                        :class="isDark?'bg-gray-700 border-gray-800':'bg-gray-200 border-white'"
                    >
                        <div class="px-2 py-2">
                            <p class="text-lg">
                                {{  item.applicantName }}
                            </p>
                            <p class="">
                                Duration: {{ item.sDate }} - {{ item.eDate }}
                            </p>
                            <p class="" v-for="task in item.tasks">
                                →{{ task }} 
                            </p>
                        </div>
                    </div>
                </template>
            </VueScrollingTable>
        </div>
    </div>
    <div v-else class="subpage-height w-full" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="h-[15%] 1080:h-[12%] 1440:h-[9%] 4k:h-[6%]">
            <p class="font-bold laptop:text-3xl 1080:text-5xl">
                Your Substitutions
            </p>
            <p class="laptop:pt-4 laptop:text-xl 1080:text-2xl">
                You have agreed to substitute for the following:
            </p>
        </div>
        <div class="h-[85%] 1080:h-[88%] 1440:h-[91%] 4k:h-[94%] border" :class="isDark?'border-gray-400':'border-black'">
            <VueScrollingTable
                class=""
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
                :class="isDark?'scrollbar-dark':''"
            >
                <template #tbody>
                    <div v-for="item in substitutions" :key="item.id"
                        class="border-b-8"
                        :class="isDark?'bg-gray-700 border-gray-800':'bg-gray-200 border-white'"
                    >
                        <div class="px-2 py-2">
                            <p class="text-xl 1080:text-2xl 1440:text-3xl 4k:text-4xl">
                                {{  item.applicantName }}
                            </p>
                            <p class="text-lg 1080:text-xl 1440:text-2xl">
                                Duration: {{ item.sDate }} - {{ item.eDate }}
                            </p>
                            <p class="text-lg 1080:text-xl" v-for="task in item.tasks">
                                →{{ task }} 
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
    height: calc(0.95 * 93vh - 3rem);
}
.subpage-height-mobile {
    height: calc(0.95 * 93vh - 1.5rem);
}
</style>