<script setup>
import { Link } from '@inertiajs/vue3';

let props = defineProps({ stripBG: String, href: String });
</script>
<template>
    <Link :href="props.href" class="flex flex-col justfiy-between rounded-3xl 1440:mx-4 text-2xl text-center mx-2">
        <div class="font-bold text-sm 1080:text-xl 1440:text-2xl 4k:text-4xl 1440:p-4 justify-center mt-4 mx-6" >
            <slot />
        </div>
        <div class="flex flex-col 1440:pt-4 my-auto py-2 1080:px-4 items-center">
            <slot name="content" />
        </div>
        <slot name="strip" />
    </Link>
    
</template>