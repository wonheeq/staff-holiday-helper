<script setup>
import EditRole from "@/Components/Manager/EditRoles.vue"
import { ref, computed } from 'vue';
import StaffInfo from "@/Components/Manager/StaffInfo.vue"
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useStaffStore } from '@/stores/StaffStore';
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3'
const page = usePage();
const user = computed(() => page.props.auth.user);
let staffStore = useStaffStore();
const { staffValue, searchStaff} = storeToRefs(staffStore);
const { fetchStaffMembers } = staffStore;

onMounted(() => {
    fetchStaffMembers(user.value.accountNo);
});
let props = defineProps({ source: Object });
let showEditModal = ref(false);
let staffRoles = ref(null);
let staffInfo = ref(null);

let fetchRolesForStaff = async(accountNo) => {
    try {
        const resp = await axios.get('/api/getRolesForStaffs/' + accountNo);
        const resp2 = await axios.get('/api/getSpecificStaffMember/' + accountNo);
        staffRoles = resp.data;
        staffInfo = resp2.data;
        alert(staffRoles);

    } catch (error) {
        alert("Failed to load data: Please try again");
        console.log(error);
    }
}; 

async function handleEditRoles(accountNo) {
    await fetchRolesForStaff(accountNo);
    showEditModal.value = true;
}
function handleDeleteRole(currentUnitId, currentRoleName){
    // alert(currentUnitId);
    // alert(currentRoleName);
    // staffRoles = staffRoles.filter(staffRole => staffRole.unitId !== currentUnitId && staffRole !== currentRoleName);
    // alert(staffRoles);
}
let deadAreaColor = "#FFFFFF";
</script>
<template>
    <div class="subpage-height w-full">
        <div class="h-[7%]">
            <p class="font-bold text-2xl laptop:text-base 1080:text-3xl 1440:text-5xl 4k:text-7xl">
                Your Staff Members:
            </p>
        </div>
        <div class="staff-text grid grid-cols-6 bg-white mr-4 gap-x-2 mx-10 ml-6">
            <div>
              <p><b>Name:</b></p>
            </div>
            <div>
              <p><b>ID:</b></p>
            </div>
            <div>
              <p><b>Email Address:</b></p>
            </div>
            <div>
              <p><b>On Leave:</b></p>
            </div>
            <div>
              <p><b>Pending Application:</b></p>
            </div>
          </div>
            <div class="bg-white 1440:mx-4 1440:mb-4 scroll">
                <VueScrollingTable
                    :deadAreaColor="deadAreaColor"
                    :scrollHorizontal="false"
                >
                    <template #tbody>
                        <div v-for="item in searchStaff" :key="item.id" class="mb-2 row-divider pt-2">
                            <StaffInfo
                                :source="item"
                                @editRoles="handleEditRoles(item.accountNo)"
                            ></StaffInfo>
                        </div>
                    </template>
                </VueScrollingTable>
                
            </div>
            <div class="pt-2 font-bold text-sm laptop:text-base 1080:text-3xl 1440:text-5xl 4k:text-7xl">
                <p ><b>Staff name or ID</b></p>
                <div>
                    <input 
                    class="search-input" 
                    ref="searchInput" 
                    v-model="staffValue"
                    placeholder="Enter your search" >
                  </div>
            </div>
            <Teleport to="body">
                <EditRole
                    v-show="showEditModal"
                    :staffRoles="staffRoles"
                    :staffInfo="staffInfo"
                    @removeRole="(currentUnitId, currentRoleName) => handleDeleteRole(currentUnitId, currentRoleName)"
                    @close="showEditModal=false;"
                />
            </Teleport>
    </div>
    
</template>
<style>
.subpage-height {
    height: calc(0.95 * (93vh - 3rem));
}
.scroll {
    overflow-y: auto;
    height: 70%;
  }
.filter-label {
    font-size: 18px; 
}
.hide {
    display: none;
}
.row-divider::after {
    content: "";
    display: block;
    width: 96.5%;
    height: 1px;
    background-color: #ccc;
    margin-top: 10px; 
}
.search-input {
    border: 2px solid #ccc;
    padding: 8px;
    width: 20%;
    border-radius: 4px; 
    text-align: left;
}
.search-button {
    margin-left: 20px;  
    background-color: #ccc;
    border: none;
    padding: 8px 50px; 
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}
/* 1080p */
@media 
(min-width: 1920px) {
    .staff-text{
        font-size: 25px;
    }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .staff-text{
        font-size: 25px;
    }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .staff-text{
        font-size: 25px;
    }
}
</style>