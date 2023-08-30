<script setup>
import StaffInfo from "@/Components/Manager/StaffInfo.vue"
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useStaffStore } from '@/stores/StaffStore';
import { onMounted } from 'vue';


let staffStore = useStaffStore();
const { staffValue, searchStaff} = storeToRefs(staffStore);
const { fetchStaffMembers } = staffStore;
let emit = defineEmits(["editRoles"]);


onMounted(() => {
    fetchStaffMembers();
});


let deadAreaColor = "#FFFFFF";


</script>
<template>
    <div class="subpage-height w-full">
        <div class="h-[7%]">
            <p class="font-bold text-5xl">
                Your Staff Members:
            </p>
        </div>
        <div class="grid grid-cols-6 bg-white mr-4 gap-x-2 mx-10">
            <div>
              <p style="font-size: 25px;"><b>Name:</b></p>
            </div>
            <div>
              <p style="font-size: 25px;"><b>ID:</b></p>
            </div>
            <div>
              <p style="font-size: 25px;"><b>Email Address:</b></p>
            </div>
            <div>
              <p style="font-size: 25px;"><b>On Leave:</b></p>
            </div>
            <div>
              <p style="font-size: 25px;"><b>Pending Application:</b></p>
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
                                @editRoles="$emit('editRoles', item.accountNo)"
                            ></StaffInfo>
                        </div>

                    </template>
                </VueScrollingTable>
                
            </div>
            <div style="font-size: 25px; padding-left: 30px;">
                <p ><b>Staff name or ID</b></p>
                <div>
                    <input 
                    class="search-input" 
                    ref="searchInput" 
                    v-model="staffValue"
                    placeholder="Enter your search" >
                  </div>
            </div>
            
           
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
.filter-radio {
    transform: scale(1.5);
    margin-right: 10px;
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
</style>