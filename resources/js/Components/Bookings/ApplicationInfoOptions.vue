<script setup>
import { useDark } from "@vueuse/core";
const isDark = useDark();
let props = defineProps({
    status: String
});

let emit = defineEmits(['cancelApplication', 'editApplication']);
function handleCancelApplication() {
    emit('cancelApplication');
}

function handleEditApplication() {
    emit('editApplication');
}
</script>
<template>
    <div class="mt-auto">
        <button @click="handleCancelApplication()" v-show="status!='C'">
            <img class="option" src="/images/delete.svg" :class="isDark?'darkModeImage':''"/>
        </button>
        <button @click="handleEditApplication()" class="ml-auto">
            <img class="option" src="/images/edit.svg" :class="isDark?'darkModeImage':''"/>
        </button>
    </div>
</template>

<style>
.option{
    height: 40px;
    width: auto;
}
</style>