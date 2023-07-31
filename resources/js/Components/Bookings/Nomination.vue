<script setup>
let props = defineProps({ role: Object });
</script>
<template>
    <div class="flex mb-2.5 ml-2.5 mt-2.5 items-center space-x-7" v-show="!role.disabled">
        <input type="checkbox" class="h-8 w-8" v-model="role.selected"/>
        <p class="text-xl">
            {{ role.role }}
        </p>
    </div>
</template>