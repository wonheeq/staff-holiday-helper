<script setup>
import Swal from 'sweetalert2';
let props = defineProps({
    status: String
});

let emit = defineEmits(['cancelApplication']);
function handleCancelApplication() {
    Swal.fire({
        title: "Cancel Application",
        text: "Are you sure you want to cancel this application?",
        showCancelButton: true,
        confirmButtonText: "Yes, cancel it",
        cancelButtonText: "No, do not cancel it",
    }).then((result) => {
        if (result.isConfirmed) {
            emit('cancelApplication');
        }
    });
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