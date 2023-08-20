<script setup>
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

let props = defineProps({
    data: Object,
});

let emit = defineEmits(['close']);
let buttonActive = ref(false);

// Disable submit button and close modal
function handleClose() {
    buttonActive.value = false;
    emit('close');
}
</script>

<template>
<Modal>
    <div class="bg-white" vif="props.data">
        <p>
            {{ props.data }}
        </p>
        <button @click="handleClose()">
                <img src="/images/close.svg"
                class="close-button h-full"
            />
        </button>
    </div>
</Modal>
</template>

<style>
.close-button {
    height: 70px;
    width: auto;
}
/* 1080p */
@media 
(min-width: 1920px) {
    .close-button {
        height: 70px;
        width: auto;
    }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .close-button {
        height: 80px;
        width: auto;
    }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .close-button {
        height: 110px;
        width: auto;
    }
}
</style>