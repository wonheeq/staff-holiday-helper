<script setup>
import NavLink from '@/Components/NavLink.vue';
import NavOption from './NavOption.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { useDark } from "@vueuse/core";
const isDark = useDark();
const page = usePage();
const user = computed(() => page.props.auth.user);
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
// const { $isMobile() } = storeToRefs(screenSizeStore);

let emit = defineEmits(['open-settings', 'log-out']);
let options = {
    left: [
        { source: "/images/home.svg", caption: "Home", minPerm: "staff" },
        { source: "/images/booking.svg", caption: "Bookings", minPerm: "staff" },
        { source: "/images/manager.svg", caption: "Manager", minPerm: "lmanager" },
        { source: "/images/admin.svg", caption: "Admin", minPerm: "sysadmin" },
    ],
    right: [
        { source: "/images/settings.svg", caption: "Settings", noLink: () => {
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

function shouldDisplayOption(minPerm) {
    // Account is an admin
    if (user.value.accountType == "sysadmin") {
        return true;
    }

    // Account meets minimum permissions
    if (user.value.accountType == minPerm) {
        return true;
    }

    // Minimum permission is lmanager AND user is temporary manager
    if (minPerm == "lmanager" && user.value.isTemporaryManager==1) {
        return true;
    }

    // Minimum required permissions = staff AKA everyone logged in can access
    if (minPerm == "staff") {
        return true;
    }

    // Minimum required permissions not met
    return false;
}


</script>
<template>
    <div class="flex flex-row justify-between border-2 rounded-md drop-shadow-md" :class="isDark?'bg-gray-800 border-gray-700':'bg-white'">
        <div class="flex flex-row laptop:space-x-4 ml-2 laptop:ml-4 my-2 items-center">
            <img src="/images/logo.svg" class="logo mr-2" :class="isDark ? 'darkModeImage':''"/>
            <div v-if="!$isMobile()" class="inline-block h-[100%] min-h-[1em] w-0.5 self-stretch bg-neutral-200 opacity-100 dark:opacity-50"></div>
            <div class="flex flex-row laptop:space-x-2 1440:space-x-4">
                <div class="flex flex-col items-center justify-center" v-for="option in options.left" >
                    <NavLink v-if="shouldDisplayOption(option.minPerm)" :href="formatLink(option.caption)" class="flex flex-col justify-center items-center">
                        <img :src="option.source" :class="isDark ? 'darkModeImage':''"/>
                        <p class="text-xs 1080:text-sm 1440:text-sm 4k:text-2xl">{{ option.caption }}</p>
                    </NavLink>
                </div>
            </div>
        </div>
        <div class="flex flex-row laptop:space-x-4 ml-2 laptop:ml-4 my-2 items-center">
            <div class="flex flex-col items-center justify-center" v-for="option in options.right" >
                <NavLink v-if="option.noLink == null" :href="formatLink(option.caption)" class="flex flex-col justify-center items-center">
                    <img :src="option.source" :class="isDark ? 'darkModeImage':''"/>
                    {{ option.caption }}
                </NavLink>
                <NavOption v-if="option.noLink"
                    class="flex flex-col justify-center items-center"
                    @click="option.noLink()"
                >
                    <img :src="option.source" :class="isDark ? 'darkModeImage':''"/>
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
