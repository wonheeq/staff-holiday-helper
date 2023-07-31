<script setup>
import LoggedInView from "@/Components/LoggedInView.vue";
import HomeShortcuts from "@/Components/HomeShortcuts.vue";
import HomeCalendarSmall from "@/Components/HomeCalendarSmall.vue";
import HomeCalendarLarge from "@/Components/HomeCalendarLarge.vue";
import HomeMessages from "@/Components/HomeMessages.vue";
import { ref } from "vue";

let user = {
    firstName: "John",
    lastName: "Smith",
    lineManager: {
        name: "Gordon Ramsey",
        id: "a12356",
    },
};

let calendarLarge = ref(false);
</script>

<template>
    <LoggedInView>
        <div class="flex screen mx-4 my-4" v-show="!calendarLarge">
            <div class="flex flex-col items-center w-4/5 1440:w-10/12 mr-4">
                <HomeShortcuts :user="user" class="h-3/6 min-w-[800px] 1080:h-2/5 1440:h-2/5 4k:h-[35%] w-3/5 1080:w-1/2"></HomeShortcuts>
                <HomeMessages class="h-3/6 1080:h-3/5 1440:h-3/5 4k:h-[65%] mt-4 drop-shadow-md"></HomeMessages>
            </div>
            <HomeCalendarSmall
                class="flex w-1/5 1440:w-2/12 drop-shadow-md"
                @enlarge-calendar="calendarLarge=true"    
            />
        </div>
        <HomeCalendarLarge
            class="screen mx-4 mt-4 drop-shadow-md"
            v-show="calendarLarge"
            @shrink-calendar="calendarLarge=false"
        />
    </LoggedInView>
</template>

<style>
.screen {
    height: calc(93vh - 3rem);
}
</style>