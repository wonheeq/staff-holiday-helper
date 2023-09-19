<script setup>
import Message from './Message.vue';
import VueScrollingTable from "vue-scrolling-table";
import { useMessageStore } from '@/stores/MessageStore';
import { onMounted, computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { useDark } from "@vueuse/core";
const isDark = useDark();
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
import { usePage } from '@inertiajs/vue3'
const page = usePage();
const user = computed(() => page.props.auth.user);
let messageStore = useMessageStore();
const { filteredMessages, viewing, unreadMessages, messages } = storeToRefs(messageStore);
const { fetchMessages } = messageStore;

let emit = defineEmits(['acceptSomeNominations', 'reviewApplication']);

onMounted(() => {
    fetchMessages(user.value.accountNo);
});

const deadAreaColor = computed(() => {
    return isDark.value ? '#1f2937': '#FFFFFF';
})
</script>

<template>
    <div class="laptop:rounded-md w-full"
    :class="isDark?'bg-transparent laptop:bg-gray-800':'bg-transparent laptop:bg-white'">
        <div v-if="isMobile" class="w-full bg-white mb-2 rounded-md">
            <div class="h-[0.25rem]"></div>
            <div v-if="unreadMessages.length" class="flex flex-row justify-between px-2 text-lg mx-1 bg-red-400 text-white p-1 rounded-3xl items-center">
                <img src="/images/warning.svg"/>
                <p class="text-center text-sm">
                    You have {{ unreadMessages.length }} unacknowleged messages.
                </p>
                <img src="/images/warning.svg"/>
            </div>
            <div class="grid grid-cols-3 p-1">
                <p class="text-xl font-bold">Messages:</p>
                <div class="col-span-2 justify-self-end flex">
                    <button
                    @click="viewing = 'all'"
                    :class="{
                        'border-black font-bold border-2': viewing === 'all',
                        'border-gray-500 text-gray-500 border-t-2 border-l-2 border-b-2': viewing === 'unread',
                    }"
                    class="px-2 border">
                        All ({{ messages.length }})
                    </button>
                    <button
                    @click="viewing = 'unread'"
                    :class="{
                        'border-black font-bold border-2': viewing === 'unread',
                        'border-gray-500 text-gray-500 border-t-2 border-r-2 border-b-2': viewing === 'all',
                    }"
                    class="px-2 border">
                        Unacknowleged ({{ unreadMessages.length }})
                    </button>
                </div>
            </div>
            <div class="bg-white border border-black mx-1 mb-1 laptop:mx-2 laptop:mb-2 1440:mx-4 1440:mb-4 scroller">
                <VueScrollingTable
                    id="messageTable"
                    :deadAreaColor="deadAreaColor"
                    :scrollHorizontal="false"
                >
                    <template #tbody>
                        <div v-for="item in filteredMessages" :key="item.id">
                            <Message :source="item"
                                @acceptSomeNominations="emit('acceptSomeNominations', item)"
                                @reviewApplication="emit('reviewApplication', item)"
                            ></Message>
                        </div>
                    </template>
                </VueScrollingTable>
                <div class="h-[4.75rem] flex flex-col justify-evenly" v-show="viewing == 'all' && messages.length == 0 || viewing == 'unread' && unreadMessages.length == 0">
                    <p class="text-center">
                        No messages to display.
                    </p>
                </div>
            </div>
            <div class="h-[0.125rem]"></div>
        </div>
        <div v-else class="flex flex-col h-full">
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
                        'border-black font-bold border-2': viewing === 'all',
                        'border-gray-500 text-gray-500 border-t-2 border-l-2 border-b-2': viewing === 'unread',
                    }"
                    class="text-lg 1080:text-2xl 1440:text-4xl 4k:text-5xl px-2 py-2 border">
                        All ({{ messages.length }})
                    </button>
                    <button
                    @click="viewing = 'unread'"
                    :class="{
                        'border-black font-bold border-2': viewing === 'unread',
                        'border-gray-500 text-gray-500 border-t-2 border-r-2 border-b-2': viewing === 'all',
                    }"
                    class="text-lg 1080:text-2xl 1440:text-4xl 4k:text-5xl px-2 py-2 border">
                        Unacknowleged ({{ unreadMessages.length }})
                    </button>
                </div>
            </div>
            <div class="bg-white border border-black mx-2 mb-2 1440:mx-4 1440:mb-4 scroller">
                <VueScrollingTable
                    :deadAreaColor="deadAreaColor"
                    :scrollHorizontal="false"
                    class=""
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
    </div>
</template>

<style>
::-webkit-scrollbar {
    height: 12px;
    width: 12px;
    background: #555555;
}

::-webkit-scrollbar-thumb {
    background: #9d9d9d;
    -webkit-border-radius: 1ex;
}

::-webkit-scrollbar-corner {
    background: #9d9d9d;
}
.scroller {
  overflow-y: auto;
  height: calc(90% - 0.5rem);
}
.warning{
    width: 2.5vw;
    height: 2.5vh;
}
</style>