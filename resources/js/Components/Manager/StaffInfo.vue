<script setup>
import EditRolesButton from './EditRolesButton.vue';
let props = defineProps({ source: Object });
let emit = defineEmits(['editRoles']);

function handleEditRoles() {
    emit('editRoles');
    
}
</script>
<template>
    <div class="grid grid-cols-6 bg-white mr-4 gap-x-2 mx-6"> 
        <div>
            <p style="font-size: 25px;">{{ source.fName }} {{ source.lName }} </p>
        </div>
        <div>
            <p style="font-size: 25px; padding-left: 5px;">{{ source.accountNo }} </p>
        </div>
        <div>
            <p style="font-size: 25px; padding-left: 10px;">{{ source.accountNo }}@curtin.edu.au</p>
        </div>
        <div>
            <p style="font-size: 25px; padding-left: 20px;">Yes</p>
        </div>
        <div>
            <p style="font-size: 25px; padding-left: 25px;">{{ source.pending }}</p>
        </div>
        <div class="pl-2">
            <EditRolesButton
                :fName="source.fName"
                @editRoles="handleEditRoles()"
            />
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
  padding: 12px 50px;
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
    font-size: 20px; 
  }
</style>