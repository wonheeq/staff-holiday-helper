<script setup>
import { storeToRefs } from 'pinia';
import { useDark } from "@vueuse/core";
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
// const { $isMobile() } = storeToRefs(screenSizeStore);
const isDark = useDark();
let props = defineProps({ source: Object });
let emit = defineEmits(['editRoles']);
</script>
<template>
    <div v-if="$isMobile()" class="text-sm" :class="isDark?'bg-gray-800':'bg-white'" >
        <div>
            <p class="staff-text"><strong>Name: </strong>{{ source.fName }} {{ source.lName }}</p>
        </div>
        <div class="shrink-1">
            <p class="staff-text"><strong>ID: </strong>{{ source.accountNo }}</p>
        </div>
        <div>
            <p class="staff-text"><strong>Email Address: </strong>{{ source.accountNo }}@curtin.edu.au</p>
        </div>
        <div>
            <p class="staff-text"><strong>On Leave: </strong>{{ source.onLeave }}</p>
        </div>
        <div>
            <p class="staff-text"><strong>Pending Application: </strong>{{ source.pending }}</p>
        </div>
        <div class="flex justify-center items-center">
            <button @click="emit('editRoles', source.accountNo)" class="roles_buttonMobile">
                <span class="button-text">View/Edit Roles</span>
            </button>
        </div>
    </div>
    <div v-else class="grid grid-cols-6 mr-4 mx-6" style="grid-auto-columns: min-content;" :class="isDark?'bg-gray-800':'bg-white'"> 
        <div>
            <p class="staff-text">{{ source.fName }} {{ source.lName }} </p>
        </div>
        <div class="shrink-1">
            <p class="staff-text">{{ source.accountNo }} </p>
        </div>
        <div>
            <p class="staff-text">{{ source.accountNo }}<br>@curtin.edu.au</p>
        </div>
        <div>
            <p class="staff-text">{{ source.onLeave }} </p>
        </div>
        <div>
            <p class="staff-text">{{ source.pending }}</p>
        </div>
        <div class="laptop:pl-5 1080:text-pl-5 1440:pl-5 4k:pl-5">
            <div >
                <button @click="emit('editRoles', source.accountNo)" class="roles_button">
                    <span class="button-text">View/Edit Roles</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style>
.roles_button {
    display: flex;
  justify-content: center;
  align-items: center; 
  background-color: #ccc; 
  border: none;
  padding: 2px 29px;
  border-radius: 4px; 
  cursor: pointer;
  transition: background-color 0.2s;
}
.roles_button:active{
    background-color: #999;
}
.roles_buttonMobile {
    display: flex;
    justify-content: center;
    align-items: center; 
    background-color: #ccc; 
    border: none;
    padding: 2px 29px;
    width: 100%;
    border-radius: 4px; 
    cursor: pointer;
    transition: background-color 0.2s;
  }
  .roles_buttonMobile:active{
      background-color: #999;
  }
.button-text {
    font-weight: bold;
    color: black; 
    font-size: 15px; 
  }

  /* 1080p */
@media 
(min-width: 1920px) {
    .roles_button {
        display: flex;
        justify-content: center;
        align-items: center; 
        background-color: #ccc; 
        border: none;
        padding: 12px 50px;
        border-radius: 4px; 
        cursor: pointer;
        transition: background-color 0.2s;
      }
      .button-text {
        font-weight: bold;
        color: black; 
        font-size: 20px; 
      }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .roles_button {
        display: flex;
        justify-content: center;
        align-items: center; 
        background-color: #ccc; 
        border: none;
        padding: 12px 50px;
        border-radius: 4px; 
        cursor: pointer;
        transition: background-color 0.2s;
      }
      .button-text {
        font-weight: bold;
        color: black; 
        font-size: 20px; 
      }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .roles_button {
        display: flex;
        justify-content: center;
        align-items: center; 
        background-color: #ccc; 
        border: none;
        padding: 12px 50px;
        border-radius: 4px; 
        cursor: pointer;
        transition: background-color 0.2s;
      }
      .button-text {
        font-weight: bold;
        color: black; 
        font-size: 20px; 
      }
}
</style>