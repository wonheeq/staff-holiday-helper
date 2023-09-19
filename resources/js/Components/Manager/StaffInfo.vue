<script setup>
let props = defineProps({ source: Object });
let emit = defineEmits(['editRoles']);
</script>
<template>
    <div class="grid grid-cols-6 bg-white mr-4 mx-6" style="grid-auto-columns: min-content;"> 
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