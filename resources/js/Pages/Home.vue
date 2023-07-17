<script setup>
import LoggedInView from "@/Components/LoggedInView.vue";
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
    <LoggedInView>
        <div class="flex screen mx-4 mt-4">
            <div class="flex flex-col items-center w-4/5 1440:w-10/12 mr-4 mb-2">
                <HomeShortcuts :user="user" class="h-3/6 1080:h-2/5 1440:h-2/5 4k:h-[35%] w-3/5 1080:w-1/2"></HomeShortcuts>
                <HomeMessages class="h-3/6 1080:h-3/5 1440:h-3/5 4k:h-[65%] mt-4 drop-shadow-md"></HomeMessages>
            </div>
            <HomeCalendar class="flex w-1/5 1440:w-2/12 mb-2 drop-shadow-md"></HomeCalendar>
        </div>
    </LoggedInView>
</template>

<style>
.screen {
    height: calc(92vh - 2rem);
}
</style>