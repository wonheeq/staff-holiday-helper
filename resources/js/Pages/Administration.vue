<script>
    
    //import Navbar from './Navbar.vue';
    import AdminTabs from '@/Components/AdminTabs.vue';

    export default{
    components:{
         'admintabs':AdminTabs
    }
}

</script>

<template>

    <!--<main>
        <div class="margin-fix"></div>
        <Navbar class="h-[7vh] mx-4"></Navbar>
       < !--<slot />-- > 
    </main>-->
 
<div class="tabarea">
    <admintabs/>  
</div>  

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
    /*body {
        background-image: url('/images/background.svg');
        background-size: cover;
        background-repeat: no-repeat;
    }*/
</style>