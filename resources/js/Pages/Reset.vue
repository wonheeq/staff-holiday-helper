<!--
    File: Landing.vue
    Purpose: Parent file for the password reset page for LeaveOnTime.
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 1/08/2023
        By: Ellis Janson Ferrall (20562768)
 -->

<script setup>
import PageLayout from "@/Layouts/PageLayout.vue";
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

const strArr = props.email.split("@");
let accountNo = strArr[0];

</script>

<template>
    <PageLayout>
        <div>

            <PasswordForm :account-no="accountNo" :token="token"></PasswordForm>
        </div>
    </PageLayout>
</template>





