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
    <div v-if="isMobile()"  class="flex space-x-4">
        <button class="w-1/3 h-8 laptop:w-80 px-2 rounded-tl-md rounded-tr-md text-xs"
            :class="{ 'bg-white': activeScreen===option.id, 'bg-gray-300': activeScreen!==option.id }"
            v-for="option in options"
            @click="activeScreen = option.id; emit('screen-changed', option.id)"
        >
        {{ option.mobileTitle }}
        </button>
    </div>
    <div v-else class="flex space-x-4">
        <button class="w-1/3 h-full laptop:w-80 px-2 rounded-tl-md rounded-tr-md"
            :class="{ 'bg-white': activeScreen===option.id, 'bg-gray-300': activeScreen!==option.id }"
            v-for="option in options"
            @click="activeScreen = option.id; emit('screen-changed', option.id)"
        >
            <p class="text-xs laptop:text-lg 1080:text-xl 1440:text-2xl text-center">
                {{ option.title }}
            </p>
        </button>
    </div>
</template>