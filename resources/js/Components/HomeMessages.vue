<script setup>
let unread = 3;
</script>

<template>
    <div class="flex flex-col bg-white w-full rounded-md">
        <div class="flex flex-row justify-between px-4 pb-2 mt-4">
            <p class="text-4xl font-bold">Messages:</p>
            <div class="text-4xl">
                <button class="px-4 border border-gray-300 border-2">
                    All
                </button>
                <button class="border border-black border-2">
                    Unacknowleged
                </button>
            </div>
        </div>
        <div class="flex flex-row justify-between absolute self-center mt-4 px-4 text-xl w-2/5 bg-red-400 text-white p-2 rounded-3xl">
            <img src="images/warning.svg" />
            <p>You have {{ unread }} unacknowleged messages.</p>
            <img src="images/warning.svg" />
        </div>
        <div class="border border-black mx-4 h-5/6 mb-4 mt-6">

        </div>
    </div>
</template>