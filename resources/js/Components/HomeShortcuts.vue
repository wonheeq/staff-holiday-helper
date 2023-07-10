<script setup>
import Shortcut from './Shortcut.vue';

let props = defineProps({ user: Object });

let copyEmail = () => {
    let email = props.user.lineManager.id + "@curtin.edu.au";
    navigator.clipboard.writeText(email); 
    alert("Email address copied to clipboard.");
};
</script>

<template>
    <div class="flex flex-col ml-4 items-center">
        <div class="flex flex-row">
            <div class="flex flex-col items-center text-2xl">
                <p>Welcome {{ props.user.firstName }},</p>
                <p>Your line manager is currently {{ props.user.lineManager.name }}
                    <input @click="copyEmail" type="image" class="h-8 align-middle" src="images/mail.svg" v-b-tooltip.hover title="Copy Email Address to Clipboard"/>
                </p>
            </div>
        </div>
        <div class="grid grid-cols-3 bg-white rounded-md p-6 w-4/5 mt-2 h-full drop-shadow-md">
            <Shortcut class="bg-green-200" href="Bookings">
                Your Leave Applications
                <template #content>
                    <ul class="text-left">
                        <li>1 Approved</li>
                        <li>1 Pending</li>
                        <li>1 Denied</li>
                    </ul>
                </template>
                <template #strip>
                    <div class="h-3 mt-auto mb-12 bg-green-400"></div>
                </template>
            </Shortcut>
            <Shortcut class="bg-orange-200 pt-20" href="Create">
                Create New Leave Application
                <template #strip>
                    <div class="h-3 mt-auto mb-12 bg-orange-400"></div>
                </template>
            </Shortcut>
            <Shortcut class="bg-purple-200" href="Subs">
                Your Substitutions
                <template #content>
                    <p>3 upcoming substitutions.</p>
                    <p class="mt-4">Next: 12 Aug</p>
                </template>
                <template #strip>
                    <div class="h-3 mt-auto mb-12 bg-purple-400"></div>
                </template>
            </Shortcut>
        </div>
    </div>
</template>