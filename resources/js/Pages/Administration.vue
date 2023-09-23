<script setup>
    import PageLayout from "@/Layouts/PageLayout.vue";
    import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
    import AdminTabs from '@/Components/Admin/AdminTabs.vue';

    let props = defineProps({
        screenProp: {
            type: String,
            default: 'default'
        }
    });
</script>

<template>
<PageLayout>
    <AuthenticatedLayout>       
            <AdminTabs :screenProp="props.screenProp"/>     
    </AuthenticatedLayout>
</PageLayout>
</template>

<style>
    body, html {
    padding: 0;
    margin: 0;
    width: 100%;
    min-height: 100vh;
    }*/

    .screen {
        height: calc(93vh - 3rem);
    }
</style>