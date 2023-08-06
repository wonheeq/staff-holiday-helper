<script setup>
import { ref } from "vue";
import axios from 'axios';

axios.defaults.withCredentials = true;

const formData = ref({
    accountNo: '123456c',
    password: 'testPassword7'
});

const formDataTwo = ref({
    accountNo: 'test',
    password: 'test'
});

const user = ref();
const errorMsg = ref('');

async function handleLogin() {
    await axios.get("/sanctum/csrf-cookie");

    await axios.post("login", {
        accountNo: formData.value.accountNo,
        password: formData.value.password,
    }).then( function(response) {

        if( response.data.response == "success") {
            window.location.href = response.data.url
            // errorMsg.value = response.data.error;
        } else {
            errorMsg.value = response.data.error;
        }
    })

    // let {data} = await axios.get("/api/user");
    // user.value = data;
};

async function handleCreate() {
    await axios.post("login/create", {
        accountNo: formData.value.accountNo,
        password: formData.value.password,
    });
}


</script>

<template>
    <div>
        {{ user }}
        {{ errorMsg }}
        <form action="#" @submit.prevent="handleLogin">
            <div>
                <input type="text" name="accountNo" v-model="formData.accountNo">
            </div>
            <div>
                <input type="password" name="password" v-model="formData.password">
            </div>
            <div>
                <button type="submit">Sign In</button>
            </div>
            <div>
                {{ formData.accountNo }}
            </div>
            <div>
                {{ formData.password }}
            </div>
        </form>



        <form class="mt-20" action="#" @submit.prevent="handleCreate">
            <div>
                <input type="text" name="accountNo" v-model="formDataTwo.accountNo">
            </div>
            <div>
                <input type="password" name="password" v-model="formDataTwo.password">
            </div>
            <div>
                <button type="submit">Create User</button>
            </div>
            <div>
                {{ formDataTwo.accountNo }}
            </div>
            <div>
                {{ formDataTwo.password }}
            </div>
        </form>
    </div>

</template>
