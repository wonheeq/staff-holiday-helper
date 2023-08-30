<script setup>
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
    <AuthenticatedLayout>
        <div class="flex flex-col screen mt-4 mx-4 drop-shadow-md">
            <div class="tabarea">
                <AdminTabs :screenProp="props.screenProp"/>  
            </div> 
        </div> 
    </AuthenticatedLayout>
</template>

<style>
    .margin-fix{
    height: 1rem;
    }

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