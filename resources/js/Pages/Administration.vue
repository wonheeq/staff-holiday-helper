<script>
    import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
    import AdminTabs from '@/Components/AdminTabs.vue';

    export default{
    components:{
         'admintabs':AdminTabs,
         'AuthenticatedLayout':AuthenticatedLayout
    }
}

</script>

<template>
    <AuthenticatedLayout>
        <div class="flex flex-col screen mt-4 mx-4 drop-shadow-md">
            <div class="tabarea">
                <admintabs/>  
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
    }

    .screen {
        height: calc(93vh - 3rem);
    }
    /*body {
        background-image: url('/images/background.svg');
        background-size: cover;
        background-repeat: no-repeat;
    }*/
</style>