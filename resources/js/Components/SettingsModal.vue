<script setup>
import { ref, watch, reactive } from 'vue';
let emit = defineEmits(['close-settings']);

let errors = reactive([]);
let displaySuccess = ref(true);
let buttonActive = ref(false);
let password = reactive({
    password: "",
    confirm: ""
});

const MIN_LENGTH = 10;
const MAX_LENGTH = 30;
const hasUppercase = new RegExp("(?=.*[A-Z])");
const hasLowercase = new RegExp("(?=.*[a-z])");
const hasWhitespace = new RegExp("/\s/");
const hasDigit = new RegExp("\\d");

// Validate and push any error messages to the error array
let validatePasswords = () => {
    errors.length = 0;

    if (!hasUppercase.test(password.password)) {
        errors.push("Password must contain at least one uppercase letter.");
    }

    if (!hasLowercase.test(password.password)) {
        errors.push("Password must contain at least one lowercase letter.");
    }

    if (password.password.length < MIN_LENGTH || password.password.length > MAX_LENGTH) {
        errors.push("Password length must be between 10 and 30.");
    }

    if (!hasDigit.test(password.password)) {
        errors.push("Password must contain at least one number.");
    }

    if (hasWhitespace.test(password.password)) {
        errors.push("Password must not contain spaces.");
    }

    // Check if passwords match and activate submit button if so
    if (password.password !== password.confirm) {
        errors.push("Passwords do not match.");
        buttonActive = false;
    }
    else if (password.password == password.confirm && errors.length == 0) {
        buttonActive = true;
    }
};

// Watch password object for changes
watch(password, () => {
    validatePasswords();
});

let handleChangePassword = () => {
    let pass = password.password;
    alert(pass);
    resetView();
    displaySuccess = true;
};

let resetView = () => {
    errors.length = 0;
    displaySuccess = false;
    buttonActive = false;
    password.password = "";
    password.confirm = "";
}
</script>
<template>
    <div class="grid place-items-center fixed inset-0 backdrop-blur-sm bg-black/60">
        <div class="bg-white w-1/5 min-w-[320px] 1080:min-w-[420px] h-fit rounded-md drop-shadow-md pl-2 pb-2 1440:pl-4 1440:pb-4">
            <div class="flex flex-row items-center justify-between">
                <p class="text-xl 1080:text-3xl 1440:text-4xl 4k:text-5xl font-bold">
                    Change Password
                </p>
                <button @click="resetView(); emit('close-settings');">
                    <img src="/images/close.svg"
                    class="close-button"
                />
                </button>
            </div>
            <div class="pr-2 pt-2 1440:pr-4 1440:pt-4 flex flex-col items-center">
                <div class="w-full">
                    <p class="text-lg 1080:xl 1440:text-2xl 4k:text-4xl">New Password:</p>
                    <input v-model="password.password"
                        class="w-full 4k:h-16 4k:text-2xl"
                        type="password"
                        @keydown.space.prevent
                        @copy.prevent
                        @paste.prevent 
                    />
                </div>
                <div class="pt-2 1440:pt-4 w-full">
                    <p class="text-lg 1080:xl 1440:text-2xl 4k:text-4xl">Confirm New Password:</p>
                    <input v-model="password.confirm"
                        class="w-full 4k:h-16 4k:text-2xl"
                        type="password"
                        @keydown.space.prevent
                        @copy.prevent
                        @paste.prevent
                    />
                </div>
                <div class="w-full pt-2 1440:pt-4" v-show="errors.length > 0">
                    <p class="text-xs 1440:text-base 4k:text-xl text-red-500 w-full text-center"
                        v-for="msg in errors"
                    >
                        {{ msg }}
                    </p>
                </div>
                <button class="w-full rounded py-2 1440:py-4 4k:py-6 mt-2 1440:mt-4 font-bold text-lg 1440:text-2xl 4k:text-4xl"
                    :class="{
                        'bg-blue-300': buttonActive,
                        'bg-gray-300': !buttonActive
                    }"
                    :disabled="!buttonActive"
                    @click="handleChangePassword()"
                >
                    Change Password
                </button>
                <p class="text-xs 1080:text-sm 4k:text-xl w-full text-center mt-2 1440:mt-4 bg-cyan-100 p-4 border border-black rounded-md text-blue-800 font-bold"
                    v-show="displaySuccess"
                >
                    Your password has been changed successfully!
                </p>
            </div>
        </div>
    </div>
</template>
<style>
.close-button {
    height: 40px;
    width: auto;
}
/* 1080p */
@media 
(min-width: 1920px) {
    .close-button {
        height: 56px;
        width: auto;
    }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .close-button {
        height: 60px;
        width: auto;
    }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .close-button {
        height: 80px;
        width: auto;
    }
}
</style>