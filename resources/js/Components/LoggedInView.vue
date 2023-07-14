<script setup>
import Navbar from "@/Components/Navbar.vue";
</script>

<template>
    <main>
        <div class="margin-fix"></div>
        <Navbar class="h-[7vh] mx-4"></Navbar>
        <slot />
    </main>
</template>

<style>
.margin-fix{
    height: 1rem;
}
</style>