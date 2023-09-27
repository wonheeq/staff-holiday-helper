<script setup>
import Modal from '../Modal.vue';
import { ref, watch, onMounted } from 'vue';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import axios from 'axios';
import Swal from 'sweetalert2';
import { storeToRefs } from 'pinia';
import { useManagerStore } from '@/stores/ManagerStore';
import { useDark } from "@vueuse/core";
const isDark = useDark();
const managerStore = useManagerStore();
const { staffRoles, staffInfo, allUnits } = storeToRefs(managerStore);
const { fetchRolesForStaff } = managerStore;

let emit = defineEmits(['close', 'removeRole']);

let buttonActive = ref(false);
let unitCode = ref("");
let roleName = ref("");

watch([unitCode, roleName], () => {
    if (unitCode.value !== "" || roleName.value !== "") {
        buttonActive.value = true;
    }
    else {
        buttonActive.value = false;
    }
});
// Disable submit button and close modal
function handleClose() {
    buttonActive.value = false;
    unitCode.value ="";
    roleName.value ="";
    emit('close');
}
function handleAddRole(staffNo){
    
    let data = {
        'staffNo': staffNo,
        'unitCode': unitCode.value,
        'roleName': roleName.value
    };
    axios.post('/api/addStaffRole', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to add role, please try again.',
                    text: res.data.error
                });
                console.log(res);
            }
            else {
                staffRoles.value = fetchRolesForStaff(staffNo);
                Swal.fire({
                    icon: "success",
                    title: 'Successfully added role.',
                }).then(() => {
                    unitCode.value ="";
                    roleName.value ="";
                });
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to add role, please try again.',
        });
    });
}
function handleRemoveRole(staffNo, currentUnitId, currentRoleName, role){
    let data = {
        'staffNo': staffNo,
        'unitCode': currentUnitId,
        'roleName': currentRoleName
    };
    axios.post('/api/removeStaffRole', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to remove role, please try again.',
                    text: res.data.error
                });
                console.log(res);
            }
            else {
                const index = staffRoles.value.indexOf(role);
                if (index > -1) { // only splice array when item is found
                    staffRoles.value.splice(index, 1); // 2nd parameter means remove one item only
                }

                Swal.fire({
                    icon: "success",
                    title: 'Successfully removed role.',
                });
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to remove role, please try again.',
        });
    });
}
const allRoles = ['Course Coordinator', 'Major Coordinator', 'Unit Coordinator', 'Lecturer', 'Tutor'];
const disabledClass = "p-4 w-full rounded-md text-white text-2xl font-bold bg-gray-300";
const buttonClass = "p-4 w-full rounded-md text-white text-2xl font-bold";
</script>
<template>
<Modal>
    <div class="bg-white w-4/5 1080:w-1/2 1440:w-2/6 h-[32rem] 1080:h-[48rem] rounded-md p-4 pt-10" v-if="staffInfo[0]">
        <div class="flex h-[10%] items-center justify-between">
            <div class="flex flex-col">
              <p style="font-size: 25px;"><b>Staff Name: {{staffInfo[0].fName}} {{staffInfo[0].lName}} </b> </p>
              <p style="font-size: 25px;"><b>Staff ID: {{ staffInfo[0].accountNo }}</b></p>
              <p style="font-size: 25px;"><b>Roles: </b></p>
            </div>
            <button class="h-full" @click="handleClose()">
              <img src="/images/close.svg" class="h-full w-full" />
            </button>
          </div>
          <div class="h-[70%] py-4 pt-12 scrolling">
            <VueScrollingTable :scrollHorizontal="false">
                <template #tbody>
                    <div class="bg-white  pt-5 pl-5 flex items-center justify-between border-b border-gray-300 pb-3" v-for="role in staffRoles" :key="role.id">
                        <p class="1080:text-lg 4k:text-2xl">
                            {{ role.original.id}} {{ role.original.name }} - {{ role.original.roleName }}
                        </p>
                        <button 
                        class="roles_button"
                        @click="handleRemoveRole(staffInfo[0].accountNo, role.original.id, role.original.roleName, role)"
                        >
                            <span class="button-text">Remove</span>
                        </button>
                    </div>
                    <div class="text-center text-3xl mt-20" v-if="Object.keys(staffRoles).length === 0">
                        <strong>This staff does not have any roles currently.</strong>
                    </div>
                </template>
            </VueScrollingTable>
        </div>
        <div>
            <p style="font-size:25px; padding-bottom:20px"><b>Add Role: </b></p>
        </div>
        <div class="h-[10%]">
            <div class="flex ">
                <div>
                    <p style="font-size:20px;"><b>Unit Code: </b></p>
                    <v-select v-model="unitCode" label="Select" :options="allUnits" class="short-dropdown" :class="isDark ? 'dropdown-dark':''"></v-select>
                </div>
                <div class="pl-5">
                    <p style="font-size:20px;"><b>Role Name: </b></p>
                    <v-select v-model="roleName" label="Select" :options="allRoles" class="short-dropdown" :class="isDark ? 'dropdown-dark':''"></v-select>
                </div>
                <div class="ml-auto pt-2 pr-5">
                    <button
                    :class="unitCode=='' || roleName=='' ? disabledClass : buttonClass + ' bg-black'" 
                    :disabled="!buttonActive"
                    @click="handleAddRole(staffInfo[0].accountNo)"
                >
                    <span >Add Role  </span>
                </button>
                </div>
            </div>
        </div>
    </div>
</Modal>
</template>
<style>

.scrolling {
    overflow-y: auto;
    height: 60%;
    background-color: white;
  }
.searchbox {
    border: 2px solid #ccc;
    padding: 8px;
    width: 100%;
    border-radius: 4px; 
    text-align: left;
}
.addRoleButton {
    display: flex;
    justify-content: right;
    align-items: center; 
    background-color: #ccc; 
    border: none;
    padding: 12px 50px;
    border-radius: 4px; 
    cursor: pointer;
    transition: background-color 0.2s;
  }
  .addRoleButton:active{
      background-color: #999;
  }
  .short-dropdown .vs__dropdown-menu {
    background-color: white; 
    width: 250px;
    border: solid; 
    border-color: #6b7280; 
    border-width: 1px;
    --vs-border-style: none; 
    --vs-search-input-placeholder-color: #6b7280;
    max-height: 60px;
  }
  .short-dropdown .vs__dropdown-toggle {
    width: 250px; /* Set the desired width here */
  }
  /* 1080p */
@media 
(min-width: 1920px) {
    .short-dropdown .vs__dropdown-menu {
        background-color: white; 
        width: 300px;
        border: solid; 
        border-color: #6b7280; 
        border-width: 1px;
        --vs-border-style: none; 
        --vs-search-input-placeholder-color: #6b7280;
        max-height: 100px;
      }
      .short-dropdown .vs__dropdown-toggle {
        width: 300px; /* Set the desired width here */
      }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .short-dropdown .vs__dropdown-menu {
        background-color: white; 
        width: 300px;
        border: solid; 
        border-color: #6b7280; 
        border-width: 1px;
        --vs-border-style: none; 
        --vs-search-input-placeholder-color: #6b7280;
        max-height: 100px;
      }
      .short-dropdown .vs__dropdown-toggle {
        width: 300px; /* Set the desired width here */
      }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .short-dropdown .vs__dropdown-menu {
        background-color: white; 
        width: 300px;
        border: solid; 
        border-color: #6b7280; 
        border-width: 1px;
        --vs-border-style: none; 
        --vs-search-input-placeholder-color: #6b7280;
        max-height: 100px;
      }
      .short-dropdown .vs__dropdown-toggle {
        width: 300px; /* Set the desired width here */
      }
}
</style>