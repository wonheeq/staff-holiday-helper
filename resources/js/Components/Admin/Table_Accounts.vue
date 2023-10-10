<script setup>

import 'vue-good-table-next/dist/vue-good-table-next.css';
import { VueGoodTable } from 'vue-good-table-next';
import { useDark } from "@vueuse/core";
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { computed } from 'vue';
const pageDropdown = computed(() => {
    if (isMobile.value) {
        return [10,20,30];
    }
    return [10,20,30,40,50];
});
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const isDark = useDark();

</script>

<script>
import axios from "axios";
import Swal from 'sweetalert2';
import Swal from 'sweetalert2';

export default {
    props: {
        user: {
            type: String,
            required: true
        }
    },
    data: function() {
        let defaultC = 354;
        let defaultC = 354;
        return {
            columns: [
                {
                label: 'Account ID',
                field: 'accountNo',              
                field: 'accountNo',              
                },
                {
                label: 'Account Type',
                field: 'accountType',
                },
                {
                label: 'Surname',
                field: 'lName',
 
                },
                {
                label: 'First/Other Names',
                field: 'fName',
                },
                {
                label: 'School',
                field: 'schoolId',
                },
                {
                label: 'Line Manager',
                field: 'superiorNo',
                },
                {
                label: 'Created/Last Updated (UTC)',
                label: 'Created/Last Updated (UTC)',
                field: 'updated_at',
                },
                {
                label: '',
                field: 'delete',
                sortable: false
                },
                {
                label: '',
                field: 'delete',
                sortable: false
                }
            ],
            accounts: [],
            c: defaultC,
            tHeight: ((0.8889 * window.innerHeight) - defaultC).toFixed(0) + "px"
            c: defaultC,
            tHeight: ((0.8889 * window.innerHeight) - defaultC).toFixed(0) + "px"
        };
    },
    created() {
        //console.warn("/api/allAccounts/" + this.user)
        axios.get("/api/allAccounts/" + this.user)
        .then((response) => {
            this.accounts = response.data;
            //console.log(response.data);
        })
        .catch((error) => {
            console.log(error);
        });
        if (screen.width >= 3840) {          
            this.c = 468;
            this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        }
        if (screen.width >= 3840) {          
            this.c = 468;
            this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        }
    },
    // Using height of window to determine max table height
    mounted() {
        this.$nextTick(() => {
            window.addEventListener('resize', this.onResize);
            //console.warn("tHeight: ", this.tHeight)
        })
    },
    beforeDestroy() { 
        window.removeEventListener('resize', this.onResize); 
    },
    methods: {  
        onResize() {
        this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        //this.tHeight = (window.innerHeight).toFixed(0) + "px"
        //console.warn("tHeight: ", this.tHeight)
        },
        deleteClicked: function(rowId) {
            //console.log(rowId);
            Swal.fire({
                icon: 'warning',
                title: 'Delete \'' + rowId + '\'?',
                text: 'This will not only remove the account from the database, but also all applications, nominations, account roles, and messages associated in any way with the account.',
                showDenyButton: true,
                confirmButtonText: 'Yes',
                confirmButtonColor: '#22C55E',
            })
            .then((result) => {
                if (result.isConfirmed) {
                    this.deleteEntry(rowId);
                }
            });
        },
        deleteEntry: function(rowId) {
            //console.log('deleting');

            let data = {
                'table': 'accounts',
                'entryId': rowId
            }

            // Removing Account from DB
            axios.post("/api/dropEntry/" + this.user, data)
            .then((response) => {
                if (response.status == 200) {   
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully deleted account.'
                    });

                    // Reset Table
                    axios.get("/api/allAccounts/" + this.user)
                    .then((response) => {
                        this.accounts = response.data;
                        //console.log(response.data);
                    })
                    .catch((error) => {
                        console.log(error);
                    });                 
                }
            })
            .catch((error) => {
                console.log(error);

                Swal.fire({
                    icon: "error",
                    title: 'Error',
                    text: error.response.data.error
                });
            });
        },
        editAttribute: function(params) {
            if (params.column.field != 'delete') {
                let editable = {
                    'Account Number': params.row.accountNo,
                    'Account Type': params.row.accountType,
                    'Surname': params.row.lName,
                    'First/Other Names': params.row.fName,
                    'School Code': params.row.schoolId,
                    'Line Manager': params.row.superiorNo
                }
    
                this.$emit('toggleEditing', editable); 
            }     
        }
        deleteClicked: function(rowId) {
            //console.log(rowId);
            Swal.fire({
                icon: 'warning',
                title: 'Delete \'' + rowId + '\'?',
                text: 'This will not only remove the account from the database, but also all applications, nominations, account roles, and messages associated in any way with the account.',
                showDenyButton: true,
                confirmButtonText: 'Yes',
                confirmButtonColor: '#22C55E',
            })
            .then((result) => {
                if (result.isConfirmed) {
                    this.deleteEntry(rowId);
                }
            });
        },
        deleteEntry: function(rowId) {
            //console.log('deleting');

            let data = {
                'table': 'accounts',
                'entryId': rowId
            }

            // Removing Account from DB
            axios.post("/api/dropEntry/" + this.user, data)
            .then((response) => {
                if (response.status == 200) {   
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully deleted account.'
                    });

                    // Reset Table
                    axios.get("/api/allAccounts/" + this.user)
                    .then((response) => {
                        this.accounts = response.data;
                        //console.log(response.data);
                    })
                    .catch((error) => {
                        console.log(error);
                    });                 
                }
            })
            .catch((error) => {
                console.log(error);

                Swal.fire({
                    icon: "error",
                    title: 'Error',
                    text: error.response.data.error
                });
            });
        },
        editAttribute: function(params) {
            if (params.column.field != 'delete') {
                let editable = {
                    'Account Number': params.row.accountNo,
                    'Account Type': params.row.accountType,
                    'Surname': params.row.lName,
                    'First/Other Names': params.row.fName,
                    'School Code': params.row.schoolId,
                    'Line Manager': params.row.superiorNo
                }
    
                this.$emit('toggleEditing', editable); 
            }     
        }
    }
};

let onSearch = () => {
};
</script>


<template>
    <div class="parent1">
        <div class="laptop:mx-4 laptop:mt-4 4k:mt-8">
            <div remove-tailwind-bg>
                <VueGoodTable
                    :theme="isDark?'nocturnal':''"
                    :rows="accounts"
                    :columns="columns"
                    v-on:cell-click="editAttribute"          
                    v-on:cell-click="editAttribute"          
                    v-bind:max-height= tHeight
                    :fixed-header="!isMobile"
                    :search-options="{
                        enabled: true,
                        placeholder: 'Search Staff Accounts',
                    }"
                    :pagination-options="{
                        enabled: true,
                        //mode: 'pages',
                        perPage: 30,
                        perPageDropdown: pageDropdown
                    }">
                    <template #table-actions>
                        <p class="w-[8.5rem] mt-1 4k:text-xl">
                            Click a row to edit
                        </p>
                    </template>
                    <template #table-row="props">
                        <span v-if="props.column.field == 'delete'">
                            <button type="button" class="4k:w-10 4k:h-10" v-on:click="deleteClicked(props.row.accountNo)">
                                <img src="/images/delete.svg" :class="isDark?'darkModeImage':''"/>
                            </button>
                        </span>
                    </template>
                    <template #emptystate>
                        No entries found!
                    </template>     
                    </template>     
                </VueGoodTable> 
            </div>           
       </div>
    </div>
</template>

<style>
    .parent1 {
        width: 100%;       
    }
</style>