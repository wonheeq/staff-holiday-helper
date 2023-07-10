<script setup>
import { Link } from '@inertiajs/vue3';

let props = defineProps({ stripBG: String, href: String });
</script>
<template>
    <Link :href="props.href" class="flex flex-col justfiy-between rounded-3xl mx-2 text-2xl text-center h-full">
        <div class="font-bold text-2xl p-4 justify-center mt-4" >
            <slot />
        </div>
        <div class="flex flex-col pt-8 px-4 items-center">
            <slot name="content" />
        </div>
        <slot name="strip" />
    </Link>
    
</template>