<script setup>
//import NavLink from '@/Components/NavLink.vue';
const props = defineProps({
    options: Object,
    activeScreen: String,
});
const emit = defineEmits(['screen-changed']);
function isMobile() {
    if( screen.availWidth <= 760 ) {
        return true;
    }
    else {
        return false;
    }
}
</script>
<template>
    <div class="flex space-x-4">
        <button class="w-1/3 laptop:w-80 px-2 rounded-tl-md rounded-tr-md"
            :class="{ 'bg-white': activeScreen===option.id, 'bg-gray-300': activeScreen!==option.id }"
            v-for="option in options"
            @click="activeScreen = option.id; emit('screen-changed', option.id)"
        >
            <p v-if="isMobile()" class="text-xs">
                {{ option.mobileTitle }}
            </p>
            <p v-else class="text-xs laptop:text-lg 1080:text-xl 1440:text-2xl text-center">
                {{ option.mobileTitle }}
            </p>
        </button>
    </div>
</template>