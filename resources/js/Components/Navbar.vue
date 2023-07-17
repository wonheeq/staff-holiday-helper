<script setup>
import NavLink from '@/Components/NavLink.vue';

let options = {
    left: [
        { source: "images/home.svg", caption: "Home" },
        { source: "images/booking.svg", caption: "Bookings" },
        { source: "images/manager.svg", caption: "Manager" },
        { source: "images/admin.svg", caption: "Admin" },
    ],
    right: [
        { source: "images/account.svg", caption: "Settings" },
        { source: "images/logout.svg", caption: "Logout" },
    ]
};
</script>

<template>
    <div class="flex flex-row justify-between border-2 rounded-md bg-white drop-shadow-md">
        <div class="flex flex-row space-x-4 ml-4 my-2 items-center">
            <img src="images/logo.svg" class="logo"/>
            <div class="inline-block h-[100%] min-h-[1em] w-0.5 self-stretch bg-neutral-200 opacity-100 dark:opacity-50"></div>
            <div class="flex flex-row space-x-2 1440:space-x-4">
                <div class="flex flex-col justify-center items-center" v-for="option in options.left" >
                    <NavLink :href="option.caption.toLowerCase()" class="flex flex-col justify-center items-center">
                        <img :src="option.source"/>
                        <p class="text-xs 1080:text-sm 1440:text-sm">{{ option.caption }}</p>
                    </NavLink>
                </div>
            </div>
        </div>
        <div class="flex flex-row space-x-2 1440:space-x-4 mr-2 my-2">
            <div class="flex flex-col items-center justify-center" v-for="option in options.right" >
                <NavLink :href="option.caption.toLowerCase()" class="flex flex-col justify-center items-center">
                    <img :src="option.source"/>
                    <p class="text-xs 1080:text-sm 1440:text-sm">{{ option.caption }}</p>
                </NavLink>
            </div>
        </div>
    </div>
</template>

<style>
img{
    height: 22px;
    width: 22px;
}
.logo{
    height: 130%;
    width: auto;
}
/* 1080p */
@media 
(min-width: 1290px) {
    img {
        height: 38px;
        width: 38px;
    }
    .logo{
        height: 110%;
        width: auto;
    }
}
/* 1440p */
@media 
(min-width: 1930px) {
    img {
        height: 50px;
        width: 50px;
    }
    .logo{
        height: 105%;
        width: auto;
    }
}
</style>