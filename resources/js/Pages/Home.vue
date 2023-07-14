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
        <div class="margin-fix"></div>
        <Navbar class="h-[7vh] mx-4"></Navbar>
        <div class="flex screen mx-4 mt-4">
            <div class="flex flex-col items-center w-10/12 mr-4">
                <HomeShortcuts :user="user" class="h-2/5 w-3/5"></HomeShortcuts>
                <HomeMessages class="h-3/5 mt-4 drop-shadow-md"></HomeMessages>
            </div>
            <HomeCalendar class="h-full w-2/12 mb-4 drop-shadow-md"></HomeCalendar>
        </div>
    </main>
</template>

<style>
.screen {
    height: calc(92vh - 2rem);
}
.margin-fix{
    height: 1rem;
}
</style>