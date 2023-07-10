<script setup>
import Navbar from "@/Components/Navbar.vue";
import HomeShortcuts from "@/Components/HomeShortcuts.vue";
import HomeCalendar from "@/Components/HomeCalendar.vue";
import HomeMessages from "@/Components/HomeMessages.vue";

let user = {
    firstName: "John",
    lastName: "Smith",
    lineManager: {
        name: "Gordon Ramsey",
        id: "a12356",
    },
};
</script>

<template>
    <main>
        <div class="h-4"></div>
        <Navbar class="h-24 mx-4"></Navbar>
        <div class="flex flex-row mt-12">
            <div class="flex flex-col w-9/12 items-center">
                <HomeShortcuts :user="user" class="h-30"></HomeShortcuts>
                <HomeMessages class="ml-8 mt-6 h-41 drop-shadow-md	"></HomeMessages>
            </div>
            <HomeCalendar class="h-72.5 ml-12 w-3/12 mr-4 mb-4 drop-shadow-md	"></HomeCalendar>
        </div>
    </main>
</template>