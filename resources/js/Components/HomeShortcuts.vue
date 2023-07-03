<script setup>
import Shortcut from './Shortcut.vue';

let props = defineProps({ user: Object });

let copyEmail = () => {
    alert("Todo: implement copy to clipboard");
};
</script>

<template>
    <div class="flex flex-col ml-4 w-4/5 items-center">
        <div class="flex flex-row">
            <div class="flex flex-col items-center text-2xl">
                <p>Welcome {{ props.user.firstName }},</p>
                <p>Your line manager is currently {{ props.user.lineManager }}
                    <input @click="copyEmail" type="image" class="ml-1 h-8 align-middle" src="images/mail.svg"/>
                </p>
            </div>
        </div>
        <div class="grid grid-cols-3 bg-white rounded-md p-6 w-3/5 mt-2 h-full">
            <Shortcut class="bg-green-200">
                Your Leave Applications
                <template #content>
                    <ul class="text-left px-8 pt-4">
                        <li>1 Approved</li>
                        <li>1 Pending</li>
                        <li>1 Denied</li>
                    </ul>
                </template>
            </Shortcut>
            <Shortcut class="bg-orange-200 pt-20">Create New Leave Application</Shortcut>
            <Shortcut class="bg-purple-200">
                Your Substitutions
                <template #content>
                    <p class="text-center px-8 pt-4">3 upcoming substitutions.</p>
                    <p class="text-center px-8 pt-4">Next: 12 Aug</p>
                </template>
            </Shortcut>
        </div>
    </div>
</template>