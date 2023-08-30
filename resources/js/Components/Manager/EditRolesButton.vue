<script setup>
let props = defineProps({
    status: String
});

let emit = defineEmits(['editRoles']);
function handleEditRoles() {
    emit('editRoles');
    console.log('handleEditRoles method called'); // Add this line for debugging

}
</script>
<template>
    <div class="pl-20">
        <button @click="handleEditRoles()" class="roles_button">
            <span class="button-text">View/Edit Roles</span>
        </button>
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