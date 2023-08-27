<script setup>
import NavLink from '@/Components/NavLink.vue';
import NavOption from './NavOption.vue';

let emit = defineEmits(['open-settings', 'log-out']);
let options = {
    left: [
        { source: "/images/home.svg", caption: "Home" },
        { source: "/images/booking.svg", caption: "Bookings" },
        { source: "/images/manager.svg", caption: "Manager" },
        { source: "/images/admin.svg", caption: "Admin" },
    ],
    right: [
        { source: "/images/account.svg", caption: "Settings", noLink: () => {
            emit('open-settings');
        } },
        { source: "/images/logout.svg", caption: "Logout", noLink: () => {
            handleLogout();
        } },
    ],
};

let formatLink = (link) => {
    return "/" + link.toLowerCase();
};

// Post to logout method
async function handleLogout() {
    await axios.post("/logout").then(
        function(response) {
            if( response.data.response == "success") {
                window.location.href = response.data.url;
            }
        }
    )
}
function isMobile() {
    if( screen.width <= 760 ) {
        return true;
    }
    else {
        return false;
    }
}
</script>

<template>
    <div class="flex flex-row justify-between border-2 rounded-md bg-white drop-shadow-md">
        <div class="flex flex-row laptop:space-x-4 ml-2 laptop:ml-4 my-2 items-center">
            <img src="/images/logo.svg" class="logo mr-2"/>
            <div v-if="!isMobile()" class="inline-block h-[100%] min-h-[1em] w-0.5 self-stretch bg-neutral-200 opacity-100 dark:opacity-50"></div>
            <div class="flex flex-row laptop:space-x-2 1440:space-x-4">
                <div class="flex flex-col items-center justify-center" v-for="option in options.left" >
                    <NavLink :href="formatLink(option.caption)" class="flex flex-col justify-center items-center">
                        <img :src="option.source"/>
                        <p class="text-xs 1080:text-sm 1440:text-sm 4k:text-2xl">{{ option.caption }}</p>
                    </NavLink>
                </div>
            </div>
        </div>
        <div class="flex flex-row laptop:space-x-4 ml-2 laptop:ml-4 my-2 items-center">
            <div class="flex flex-col items-center justify-center" v-for="option in options.right" >
                <NavLink v-if="option.noLink == null" :href="formatLink(option.caption)" class="flex flex-col justify-center items-center">
                    <img :src="option.source"/>
                    <p class="text-xs 1080:text-sm 1440:text-sm 4k:text-2xl">{{ option.caption }}</p>
                </NavLink>
                <NavOption v-if="option.noLink"
                    class="flex flex-col justify-center items-center"
                    @click="option.noLink()"
                >
                    <img :src="option.source"/>
                    <p class="text-xs 1080:text-sm 1440:text-sm 4k:text-2xl">{{ option.caption }}</p>
                </NavOption>
            </div>
        </div>
    </div>
</template>

<style>
img{
    height: 16px;
    width: 16px;
}
.logo{
    height: 30px;
    width: auto;
}
/* laptop */
@media
(min-width: 1360px) {
    img{
        height: 22px;
        width: 22px;
    }
    .logo{
        height: 36px;
        width: auto;
    }
}
/* 1080p */
@media
(min-width: 1920px) {
    img {
        height: 38px;
        width: 38px;
    }
    .logo{
        height: 60px;
        width: auto;
    }
}
/* 1440p */
@media
(min-width: 2560px) {
    img {
        height: 50px;
        width: 50px;
    }
    .logo{
        height: 80px;
        width: auto;
    }
}
/* 2160p */
@media
(min-width: 3840px) {
    img {
        height: 70px;
        width: 70px;
    }
    .logo{
        height: 120px;
        width: auto;
    }
}
</style>
