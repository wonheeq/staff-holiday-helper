<script setup>
import ScrollListItem from './ScrollListItem.vue';
import { RecycleScroller } from 'vue-virtual-scroller';
let unread = 3;
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
        <div class="grid grid-cols-3 px-6 mt-5">
            <p class="text-4xl font-bold">Messages:</p>
            <div class="flex flex-row justify-between px-4 text-xl w-full bg-red-400 text-white p-2 rounded-3xl">
                <img src="images/warning.svg" />
                <p class="text-center">You have {{ unread }} unacknowleged messages.</p>
                <img src="images/warning.svg" />
            </div>
            <div class="text-2xl justify-self-end">
                <button class="px-4 border border-gray-300 border-2">
                    All
                </button>
                <button class="px-4 border border-black border-2">
                    Unacknowleged
                </button>
            </div>
        </div>
        <RecycleScroller
            class="scroller bg-white border border-black mx-6 mb-4 mt-6"
            :items="list"
            :item-size="10"
            :key-field="id"
            :buffer="600"
            v-slot="{ item }" >
            <ScrollListItem :source="item"></ScrollListItem>
        </RecycleScroller>
    </div>
</template>

<style>
.scroller {
  overflow-y: scroll;
}
</style>