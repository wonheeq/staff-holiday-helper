<script setup>
import Modal from '../Modal.vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import axios from 'axios';
import Swal from 'sweetalert2';
import { storeToRefs } from 'pinia';
import { useRolesStore } from '@/stores/RolesStore.js'

let emit = defineEmits(['close']);
let props = defineProps({
    staffFName: String,
    staffLName: String,
    staffId: String,
    subpageClass: String,
});

let staffRolesStore = useRolesStore();
const { roles } = storeToRefs(staffRolesStore);


function validateRoles(data) {
    errors = [];

    // Role or Unit Code is empty
    if (props.unitCode == null || props.roleName == null) {
        errors.push("Unit Code or role name cannot be empty")
    }

    //TO DO: Invalid unit code/ invalid role name

    return errors.length == 0;
}

function handleEditRoles(data) {
    if (validateRoles(data)) {
        //maybe update role array
        
        axios.post('/api/editRoles', data)
            .then(res => {
                if (res.status == 200) {
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully edited roles.'
                    }).then(() => {
                        emit('close');
                    });
                }
                else {
                    Swal.fire({
                        icon: "error",
                        title: 'Error',
                        text: res.data,
                    });
                }
            }).catch(err =>
            {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:  err
                });
        });
    }
    else {
        Swal.fire({
           icon: "error",
           title: "Error",
           text:  errors
        });
    }
}
</script>
<template>
<Modal>
    <div class="bg-white w-4/5 1080:w-1/2 1440:w-2/6 h-[32rem] 1080:h-[48rem] rounded-md p-4 pt-10">
        <div class="flex h-[10%] items-center justify-between">
            <div class="flex flex-col">
              <p style="font-size: 25px;"><b>Staff Name: {{ props.staffFName }} {{ props.staffLName }} </b> </p>
              <p style="font-size: 25px;"><b>Staff ID: {{ props.staffId }}</b></p>
              <p style="font-size: 25px;"><b>Roles: </b></p>
            </div>
            <button class="h-full" @click="$emit('close')">
              <img src="/images/close.svg" class="h-full w-full" />
            </button>
          </div>
          <div class="h-[70%] py-4 pt-12 scrolling">
            <VueScrollingTable :scrollHorizontal="false">
                <template #tbody>
                    <div class="bg-white  pt-5 pl-5 flex items-center justify-between border-b border-gray-300 pb-3" v-for="role in roles" :key="role.id">
                        <p class="1080:text-lg 4k:text-2xl">
                            {{ role }}
                        </p>
                        <button class="roles_button">
                            <span class="button-text">Remove</span>
                        </button>
                    </div>
                </template>
            </VueScrollingTable>
        </div>
        <div>
            <p style="font-size:25px; padding-bottom:20px"><b>Add Role: </b></p>
        </div>
        <div class="h-[10%] flex ">
            <div class="flex space-x-20">
                <div>
                    <p style="font-size:20px;"><b>Unit Code: </b></p>
                    <input class="searchbox">
                </div>
                <div>
                    <p style="font-size:20px;"><b>Role Name: </b></p>
                    <input class="searchbox">
                </div>
                <div class="pl-14">
                    <button
                    class="addRoleButton mt-6"
                    :disabled="!buttonActive"
                    @click="submitResponses()"
                >
                    <span class="button-text">Add Role  </span>
                </button>
                </div>
               
            </div>
        </div>
    </div>
</Modal>
</template>
<style>
.subpage-height {
    height: calc(0.95 * (93vh - 3rem));
}
.close-button {
    height: 70px;
    width: auto;
}
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

/* 1080p */
@media 
(min-width: 1920px) {
    .close-button {
        height: 70px;
        width: auto;
    }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .close-button {
        height: 80px;
        width: auto;
    }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .close-button {
        height: 110px;
        width: auto;
    }
}
</style>