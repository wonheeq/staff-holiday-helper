<script setup>
import { ref, watch, reactive, computed, } from 'vue';
import Modal from './Modal.vue';
import { usePage } from '@inertiajs/vue3'

let emit = defineEmits(['close-settings']);

const page = usePage();
const user = computed(() => page.props.auth.user);

let errors = reactive([]);
let displaySuccess = ref(false);
let buttonActive = ref(false);
let password = reactive({
    current: "",
    password: "",
    confirm: ""
});
let fieldType = reactive({
    password: {
        type: "password",
        image: "/images/Eye_light.svg"
    },
    confirm: {
        type: "password",
        image: "/images/Eye_light.svg"
    },
    current: {
        type: "password",
        image: "/images/Eye_light.svg"
    },
})
let switchVis = (field) => {
    if (field.type === "password" ) {
        field.type = "text";
        field.image = "/images/Eye_fill.svg";
    } else {
        field.type = "password";
        field.image = "/images/Eye_light.svg";
    }
};

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

    if (!hasDigit.test(password.password)) {
       errors.push("Password must contain at least one number.");
    }

    if (password.password.length < MIN_LENGTH || password.password.length > MAX_LENGTH) {
       errors.push("Password length must be between 10 and 30.");
    }

    if (hasWhitespace.test(password.password)) {
       errors.push("Password must not contain spaces.");
    }

    // Check if passwords match and activate submit button if so
    if (password.password !== password.confirm) {
       errors.push("Passwords do not match.");
    }

    else if (password.current == "") {
        errors.push("Please enter your current password.");
    }

    else if (password.password == password.confirm && errors.length == 0) {
       buttonActive = true;
    }
};

// Watch password object for changes
watch(password, () => {
    if( !(password.password == "" && password.confirm == "" && password.current == "")) {
        displaySuccess = false;
        validatePasswords();
    }
});


let resetView = () => {
    displaySuccess = false;
    buttonActive = false;
    password.password = "";
    password.confirm = "";
    password.current = "";
    errors.length = 0;
    fieldType.password.type = "password";
    fieldType.confirm.type = "password";
    fieldType.current.type = "password";
    fieldType.password.image = "/images/Eye_light.svg";
    fieldType.confirm.image = "/images/Eye_light.svg";
    fieldType.current.image = "/images/Eye_light.svg";
};

async function handleReset() {
    displaySuccess = false;
    await axios.post("/change-password", {
        accountNo: user.value.accountNo,
        currentPassword: password.current,
        password: password.password,
        password_confirmation: password.confirm,

    }).then( function(response) {
        resetView();
        displaySuccess = true;

    }).catch(error => {
        if(error.response) {
            errors.push(error.response.data.message);
        }
    })
}

</script>
<template>
    <Modal>
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
            <form action="#" @submit.prevent="handleReset">
                <div class="pr-2 pt-2 1440:pr-4 1440:pt-4 flex flex-col items-center">
                    <div class="w-full">
                        <p class="text-lg 1080:xl 1440:text-2xl 4k:text-4xl">Current Password:</p>
                        <div class="flex items-center h-full w-full">
                            <input v-model="password.current"
                                class="w-full 4k:h-16 4k:text-2xl"
                                :type="fieldType.current.type"
                            >
                            <button @click.prevent="switchVis(fieldType.current)" tabindex="-1" class="fixed right-5">
                                <img :src="fieldType.current.image"
                                    class="h-full w-full">
                            </button>
                        </div>
                    </div>
                    <div class="pt-2 1440:pt-4 w-full">
                        <p class="text-lg 1080:xl 1440:text-2xl 4k:text-4xl">New Password:</p>
                        <div class="flex items-center h-full w-full">
                            <input v-model="password.password"
                                class="w-full 4k:h-16 4k:text-2xl"
                                :type="fieldType.password.type"
                            >
                            <button @click.prevent="switchVis(fieldType.password)" tabindex="-1" class="fixed right-5">
                                <img :src="fieldType.password.image"
                                    class="h-full w-full">
                            </button>
                        </div>
                    </div>
                    <div class="pt-2 1440:pt-4 w-full">
                        <p class="text-lg 1080:xl 1440:text-2xl 4k:text-4xl">Confirm New Password:</p>
                        <div class="flex items-center h-full w-full">
                            <input v-model="password.confirm"
                                class="w-full 4k:h-16 4k:text-2xl"
                                :type="fieldType.confirm.type"
                            >
                            <button @click.prevent="switchVis(fieldType.confirm)" tabindex="-1" class="fixed right-5">
                                <img :src="fieldType.confirm.image"
                                    class="h-full w-full">
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-center mb-2 mt-2 text-red-500 4k:text-xl text-center">
                        <ul>
                            <li v-for="error in errors.slice(0, 1)">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                    <!-- <div class="w-full pt-2 1440:pt-4" v-show="errors.length > 0">
                        <p class="text-xs 1440:text-base 4k:text-xl text-red-500 w-full text-center"
                            v-for="msg in errors"
                        >
                            {{ msg }}
                        </p>
                    </div> -->
                    <button class="w-full rounded py-2 1440:py-4 4k:py-6 mt-2 1440:mt-4 font-bold text-lg 1440:text-2xl 4k:text-4xl"
                        :class="{
                            'bg-blue-300': buttonActive,
                            'bg-gray-300': !buttonActive
                        }"
                        :disabled="!buttonActive"
                        type="submit"
                    >
                        Change Password
                    </button>
                    <p class="text-xs 1080:text-sm 4k:text-xl w-full text-center mt-2 1440:mt-4 bg-cyan-100 p-4 border border-black rounded-md text-blue-800 font-bold"
                        v-show="displaySuccess"
                    >
                        Your password has been changed successfully!
                    </p>
                </div>
            </form>
        </div>
    </Modal>
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
