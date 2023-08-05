<script setup>
import { ref } from "vue";
import axios from 'axios';

axios.defaults.withCredentials = true;

const formData = ref({
    accountNo: 'test',
    pswd: 'test'
});

const user = ref();


async function handleLogin() {
    await axios.get("/sanctum/csrf-cookie");
    await axios.post("login", {
        accountNo: formData.value.accountNo,
        pswd: formData.value.pswd,
    });

    // let {data} = await axios.get("http://localhost:8000/api/user");
    // user.value = data;
}
</script>

<template>
    <div>
        {{ user }}
        <form action="#" @submit.prevent="handleLogin">
            <div>
                <input type="text" name="accountNo" v-model="formData.accountNo">
            </div>
            <div>
                <input type="password" name="pswd" v-model="formData.pswd">
            </div>
            <div>
                <button type="submit">Sign In</button>
            </div>
            <div>
                {{ formData.accountNo }}
            </div>
            <div>
                {{ formData.pswd }}
            </div>
        </form>
    </div>

</template>
