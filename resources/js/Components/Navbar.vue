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
            <div class="flex flex-row space-x-4">
                <div class="flex flex-col justify-center items-center" v-for="option in options.left" >
                    <NavLink :href="option.caption" class="flex flex-col justify-center items-center">
                        <img :src="option.source"/>
                        <p class="text-sm">{{ option.caption }}</p>
                    </NavLink>
                </div>
            </div>
        </div>
        <div class="flex flex-row space-x-4 mr-2 my-2">
            <div class="flex flex-col items-center justify-center" v-for="option in options.right" >
                <NavLink :href="option.caption" class="flex flex-col justify-center items-center">
                    <img :src="option.source"/>
                    <p class="text-sm">{{ option.caption }}</p>
                </NavLink>
            </div>
        </div>
    </div>
</template>

<style>
    img{
        max-height: 140%;
        min-height: 90%;
    }
    .logo{
        min-height: 80%;
        max-height: 80%;
    }
</style>

<!--
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
        <div class="flex flex-row space-x-4 ml-4 my-2">
            <img src="images/logo.svg" height="48" width="48" class="m-2" />
            <div class="inline-block h-[80px] min-h-[1em] w-0.5 self-stretch bg-neutral-200 opacity-100 dark:opacity-50"></div>
            <div class="flex flex-row space-x-4">
                <div class="flex flex-col justify-center items-center" v-for="option in options.left" >
                    <NavLink :href="option.caption" class="flex flex-col justify-center items-center">
                        <img :src="option.source" height="50" width="60"/>
                        <p class="text-sm">{{ option.caption }}</p>
                    </NavLink>
                </div>
            </div>
        </div>
        <div class="flex flex-row space-x-4 mr-2 my-2">
            <div class="flex flex-col items-center" v-for="option in options.right" >
                <NavLink :href="option.caption" class="flex flex-col justify-center items-center">
                    <img :src="option.source" height="50" width="60"/>
                    <p class="text-sm">{{ option.caption }}</p>
                </NavLink>
            </div>
        </div>
    </div>
</template>

<style>
img{
        max-width: 100%;
        max-height: 100%;
        display: block; /* remove extra space below image */
    }
    .box{
        width: 250px;        
        border: 5px solid black;
    }    
    .box.large{
        height: 300px;
    }
    .box.small{
        height: 100px;
    }
</style>
-->