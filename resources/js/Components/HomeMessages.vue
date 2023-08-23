<script setup>
import Message from './Message.vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { useMessageStore } from '@/stores/MessageStore';
import { onMounted, useAttrs } from 'vue';
const attrs = useAttrs();
import { storeToRefs } from 'pinia';
let messageStore = useMessageStore();
const { filteredMessages, viewing, unreadMessages } = storeToRefs(messageStore);
const { fetchMessages } = messageStore;

let emit = defineEmits(['acceptSomeNominations', 'reviewApplication']);

let deadAreaColor = "#FFFFFF";

await fetchMessages(attrs.auth.user.accountNo);

</script>

<template>
    <div class="flex flex-col bg-white w-full rounded-md">
        <div class="grid grid-cols-4 1440:p-4 p-2">
            <p class="text-xl 1080:text-3xl 1440:text-4xl 4k:text-6xl font-bold">Messages:</p>
            <div class="flex col-span-2 ">
                <div v-if="unreadMessages.length" class="flex flex-row justify-between px-4 text-xl w-full bg-red-400 text-white p-2 rounded-3xl items-center">
                    <img src="/images/warning.svg" class="warning"/>
                    <p class="text-center text-sm 1080:text-base 1440:text-2xl 4k:text-3xl">
                        You have {{ unreadMessages.length }} unacknowleged messages.
                    </p>
                    <img src="/images/warning.svg" class="warning"/>
                </div>
            </div>
            <div class="text-2xl justify-self-end">
                <button
                @click="viewing = 'all'"
                :class="{
                'border-black font-bold': viewing === 'all',
                'border-gray-500': viewing === 'unread',
                }"
                class="text-base 1080:text-3xl 1440:text-4xl 4k:text-6xl px-4 4k:py-2 border border-2">
                    All
                </button>
                <button
                @click="viewing = 'unread'"
                :class="{
                'border-black font-bold': viewing === 'unread',
                'border-gray-500': viewing === 'all',
                }"
                class="text-base 1080:text-3xl 1440:text-4xl 4k:text-6xl px-4 4k:py-2 border border-2">
                    Unacknowleged
                </button>
            </div>
        </div>
        <div class="bg-white border border-black mx-2 mb-2 1440:mx-4 1440:mb-4 scroller">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div v-for="item in filteredMessages" :key="item.id" class="mb-2">
                        <Message :source="item"
                            @acceptSomeNominations="emit('acceptSomeNominations', item)"
                            @reviewApplication="emit('reviewApplication', item)"
                        ></Message>
                    </div>
                </template>
            </VueScrollingTable>
        </div>
    </div>
</template>

<style>
.scroller {
  overflow-y: auto;
  height: 90%;
}
.warning{
    width: 2.5vw;
    height: 2.5vh;
}
</style>