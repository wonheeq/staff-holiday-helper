<script setup>
import EditRole from "@/Components/Manager/EditRoles.vue"
import { ref, computed } from 'vue';
import StaffInfo from "@/Components/Manager/StaffInfo.vue"
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useStaffStore } from '@/stores/StaffStore';
import { useManagerStore } from '@/stores/ManagerStore';
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { useDark } from "@vueuse/core";
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const isDark = useDark();
const managerStore = useManagerStore();
const { fetchRolesForStaff, fetchAllUnits } = managerStore;
const page = usePage();
const user = computed(() => page.props.auth.user);
let staffStore = useStaffStore();
const { staffValue, searchStaff, allUnits} = storeToRefs(staffStore);
const { fetchStaffMembers } = staffStore;
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
onMounted(() => {
    fetchStaffMembers(user.value.accountNo);
});
let props = defineProps({ source: Object });
let showEditModal = ref(false);

async function handleEditRoles(accountNo) {
    await fetchRolesForStaff(accountNo);
    await fetchAllUnits();
    showEditModal.value = true;
}
let deadAreaColor = computed(() => {
    return isDark.value ? '#1f2937': '#FFFFFF';
});
</script>
<template>
    <div v-if="isMobile" class="subpage-heightMobile2 w-full" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="h-[5%]">
            <p class="font-bold text-2xl ">
                Your Staff Members:
            </p>
        </div>
        <div class="scroll"  :class="isDark?'bg-gray-800':'bg-white'">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
                :class="isDark?'scrollbar-dark':''"
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
        <div class="pt-10 font-bold text-sm">
            <p ><b>Staff name or ID</b></p>
            <div>
                <input 
                style="border: 2px solid #ccc; padding: 8px; border-radius: 4px; text-align: left; width: 100%;"
                :class="isDark?'bg-gray-800':'bg-white'"
                ref="searchInput" 
                v-model="staffValue"
                placeholder="Enter your search" >
                </div>
        </div>
        <Teleport to="#modals">
            <EditRole
                v-show="showEditModal"
                @close="showEditModal=false;"
            />
        </Teleport>
    </div>
    <div v-else class="subpage-height w-full" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="h-[7%]">
            <p class="font-bold text-2xl laptop:text-base 1080:text-3xl 1440:text-5xl 4k:text-7xl" :class="isDark?'bg-gray-800':'bg-white'">
                Your Staff Members:
            </p>
        </div>
        <div class="staff-text grid grid-cols-6 mr-4 gap-x-2 mx-10 ml-6" :class="isDark?'bg-gray-800':'bg-white'">
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
            <div class="1440:mx-4 1440:mb-4 scroll" :class="isDark?'bg-gray-800':'bg-white'">
                <VueScrollingTable
                    :deadAreaColor="deadAreaColor"
                    :scrollHorizontal="false"
                    :class="isDark?'scrollbar-dark':''"
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
                    :class="isDark?'bg-gray-800':'bg-white'"
                    ref="searchInput" 
                    v-model="staffValue"
                    placeholder="Enter your search" >
                  </div>
            </div>
            <Teleport to="#modals">
                <EditRole
                    v-show="showEditModal"
                    @close="showEditModal=false;"
                />
            </Teleport>
    </div>
</template>
<style>
.subpage-height {
    height: calc(0.95 * 93vh - 3rem);
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
    width: 100%;
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