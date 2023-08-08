<!--
    File: Landing.vue
    Purpose: Parent file for the password reset page for LeaveOnTime.
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 1/08/2023
        By: Ellis Janson Ferrall (20562768)
 -->

<script setup>
import PasswordForm from "@/Components/Landing/PasswordForm.vue";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

</script>

<template>
    <main>
        <div>
            <div>
            </div>
            <PasswordForm :email="email" :token="token"></PasswordForm>
        </div>
    </main>
</template>





