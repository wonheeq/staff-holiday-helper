<script setup>
let props = defineProps({
    status: String
});

let emit = defineEmits(['cancelApplication']);
function handleCancelApplication() {
    emit('cancelApplication');
}
</script>
<template>
    <div class="mt-auto">
        <button @click="handleCancelApplication()" v-show="status!='C'">
            <img class="option" src="/images/delete.svg" />
        </button>
        <button class="ml-auto">
            <img class="option" src="/images/edit.svg" />
        </button>
    </div>
</template>

<style>
.option{
    height: 40px;
    width: auto;
}
</style>