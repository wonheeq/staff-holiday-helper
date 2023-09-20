<!--
    File: UnitLookup.vue
    Purpose: Vue Component for the Unit Lookup Window for use in Landing.vue
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 30/07/2023
        By: Ellis Janson Ferrall (20562768)
 -->

 <script setup>
 import { ref } from "vue";
 import axios from 'axios';
 import LandingInput from './LandingInput.vue';
import { useDark } from "@vueuse/core";
const isDark = useDark();

 let emit = defineEmits(['gotResults']);

 const formData = ref({
     code: ''
 });
 const email = ref('');
 const name = ref('');
 const unitName = ref('');
 const unitId = ref('');
 const errorMsg = ref('');

 async function handleSearch() {
     axios.post("api/getUnitDetails", {
         code: formData.value.code

     }).then( function(response) {
         emit("gotResults", response);
     }).catch(error => {
         if(error.response) {
             errorMsg.value = "Please enter a valid unit ID";
         }
     });
 }
 </script>

 <template>
 <div class="w-screen h-screen flex justify-center items-center ">
     <!-- Box/White Background -->
     <div class="w-[80%]  laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit p-5 drop-shadow-md rounded-md" :class="isDark?'bg-gray-800':'bg-white'">

         <!-- Logo -->
         <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="logo mx-auto mb-5" :class="isDark?'darkModeImage':''">

         <form action="#" @submit.prevent="handleSearch">
             <!-- Unit name/ID input -->
             <landing-input
                 title="Unit ID"
                 v-model="formData.code"
                 inType="textType" >
             </landing-input>

             <!-- Search Button -->
             <button
                 type="submit"
                 class="w-full font-bold text-2xl 4k:text-3xl p-2 mb-2"
                 :class="isDark?'bg-blue-800':'bg-blue-300'"
             >Search</button>
         </form>

         <!-- Error Message -->
         <div class="flex justify-center text-center mb-2">
             <h1 class="text-red-500 4k:text-xl">{{ errorMsg }}</h1>
         </div>

         <!-- Bottom Links -->
         <div class="flex justify-between">
             <!-- Back button -->
             <button @click="$emit('lookupBack')" class="underline font-bold 4k:text-xl">Back</button>
         </div>
     </div>
 </div>
 </template>


 <style>
 @media
 (max-width: 1360px) {

     .logo{
         height: auto;
         width: 60%;
     }
 }
 </style>