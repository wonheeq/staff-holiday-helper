<script setup>
let props = defineProps({
    index: Number,
    source: Object,
});
</script>

<template>
    <div>{{ props.index }} - {{ props.source.text }}</div>
</template>