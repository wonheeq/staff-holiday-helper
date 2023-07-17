<script setup>
import Message from './Message.vue';
import { RecycleScroller } from 'vue-virtual-scroller';
let unread = 90;
let list = [
    { 
        id: 1,
        title: 'Substitution Request',
        content: [
            'You have been asked to substitute as [Role] in [Unit Name] ([Unit Code]) in [Course Name].',
            'Duration: 00:00 02/10/2024 - 00:00 06/10/2024'
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a555555',
        titleUserName: 'John Cena',
        acknowledged: false,
    },
    {
        id: 2,
        title: 'Leave Approved',
        content: [
            'Your leave request starting 00:00 11/09/2024 has been approved.',
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a455624',
        titleUserName: 'Manager 123',
        acknowledged: false,
    },
    {
        id: 3,
        title: 'Leave Rejected',
        content: [
            'Your leave request starting 00:00 11/09/2024 has been rejected.',
            'Reason: Not enough leave left.'
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a543666',
        titleUserName: 'Admin 49',
        acknowledged: false,
    },
    { 
        id: 4,
        title: 'Substitution Request',
        content: [
            'You have been asked to substitute as [Role] in [Unit Name] ([Unit Code]) in [Course Name].',
            'Duration: 00:00 02/10/2024 - 00:00 06/10/2024'
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a555555',
        titleUserName: 'asdfaw',
        acknowledged: false,
    },
    {
        id: 5,
        title: 'Leave Approved',
        content: [
            'Your leave request starting 00:00 11/09/2024 has been approved.',
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a455624',
        titleUserName: 'Manager 32423',
        acknowledged: false,
    },
    {
        id: 6,
        title: 'Leave Rejected',
        content: [
            'Your leave request starting 00:00 11/09/2024 has been rejected.',
            'Reason: Not enough leave left.'
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a543666',
        titleUserName: 'Admin 333',
        acknowledged: false,
    },
    { 
        id: 7,
        title: 'Substitution Request',
        content: [
            'You have been asked to substitute as [Role] in [Unit Name] ([Unit Code]) in [Course Name].',
            'Duration: 00:00 02/10/2024 - 00:00 06/10/2024'
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a555555',
        titleUserName: 'John 234123',
        acknowledged: false,
    },
    {
        id: 8,
        title: 'Leave Approved',
        content: [
            'Your leave request starting 00:00 11/09/2024 has been approved.',
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a455624',
        titleUserName: 'Manager 43433',
        acknowledged: false,
    },
    {
        id: 9,
        title: 'Leave Rejected',
        content: [
            'Your leave request starting 00:00 11/09/2024 has been rejected.',
            'Reason: Not enough leave left.'
        ],
        timestamp: '14:23 11/07/2023',
        titleUserId: 'a543666',
        titleUserName: 'Admin 12',
        acknowledged: false,
    },
];
let viewing = 'unacknowledged';
</script>

<template>
    <div class="flex flex-col bg-white w-full rounded-md">
        <div class="grid grid-cols-4 1440:p-4 p-2">
            <p class="1440:text-4xl text-xl font-bold">Messages:</p>
            <div class="flex col-span-2 ">
                <div v-if="unread > 0" class="flex flex-row justify-between px-4 text-xl w-full bg-red-400 text-white p-2 rounded-3xl items-center">
                    <img src="images/warning.svg" class="warning"/>
                    <p class="text-center text-sm 1440:text-2xl">You have {{ unread }} unacknowleged messages.</p>
                    <img src="images/warning.svg" class="warning"/>
                </div>
            </div>
            <div class="text-2xl justify-self-end">
                <button class="text-base 1440:text-4xl px-4 border border-gray-300 border-2">
                    All
                </button>
                <button class="text-base 1440:text-4xl px-4 border border-black border-2">
                    Unacknowleged
                </button>
            </div>
        </div>
        <RecycleScroller
            class="scroller bg-white border border-black mx-2 mb-2 1440:mx-4 1440:mb-4 "
            :items="list"
            :item-size="10"
            :key-field="id"
            :buffer="600"
            v-slot="{ item }" >
            <Message :source="item"></Message>
        </RecycleScroller>
    </div>
</template>

<style>
.scroller {
  overflow-y: scroll;
}
.warning{
    width: 2.5vw;
    height: 2.5vh;
}
</style>