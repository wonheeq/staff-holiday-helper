
<script setup>
import PageLayout from "@/Layouts/PageLayout.vue";
import WelcomeForm from "@/Components/Landing/WelcomeForm.vue";

var hash = window.location.href.split('/').pop();

</script>

<template>
    <PageLayout>
        <div>
            <WelcomeForm :hash="hash"></WelcomeForm>
        </div>
    </PageLayout>
</template>





