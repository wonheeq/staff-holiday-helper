<script setup>

import 'vue-good-table-next/dist/vue-good-table-next.css';
import { VueGoodTable } from 'vue-good-table-next';

</script>

<script>
import axios from "axios";
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
        return {
            columns: [
            {
                label: 'Major Code',
                field: 'majorId',
                },
                {
                label: 'Major Name',
                field: 'name',
                },
                {
                label: 'Created/Last Updated (UTC)',
                field: 'updated_at',
                },
                {
                label: '',
                field: 'delete',
                sortable: false
                }
            ],
            Majors: [],
            c: defaultC,
            tHeight: ((0.8889 * window.innerHeight) - defaultC).toFixed(0) + "px"  
        };
    },
    created() {
        axios.get("/api/allMajors/" + this.user)
        .then((response) => {
            this.Majors = response.data;
            console.log(response.data);
        })
        .catch((error) => {
            console.log(error);
        });
        if (screen.width >= 3840) {          
            this.c = 468;
            this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        }
    },
    // Using height of window to determine max table height
    mounted() {
        this.$nextTick(() => {
            window.addEventListener('resize', this.onResize);
            console.warn("tHeight: ", this.tHeight)
        })
    },
    beforeDestroy() { 
        window.removeEventListener('resize', this.onResize); 
    },
    methods: {  
        onResize() {
            this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        //this.tHeight = (window.innerHeight).toFixed(0) + "px"
        //console.warn("tHeight: ", this.tHeight)
        },
        deleteClicked: function(rowId) {
            //console.log(rowId);
            Swal.fire({
                icon: 'warning',
                title: 'Delete \'' + rowId + '\'?',
                text: 'This will remove the major from the database, any account roles associated with the major will not be deleted, however the majorId attribute they have will be set to \'null\'.',
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
                'table': 'majors',
                'entryId': rowId
            }

            // Removing Major from DB
            axios.post("/api/dropEntry/" + this.user, data)
            .then((response) => {
                if (response.status == 200) {   
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully deleted Major.'
                    });

                    // Reset Table
                    axios.get("/api/allMajors/" + this.user)
                    .then((response) => {
                        this.Majors = response.data;
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
        }
    }
};

let onSearch = () => {
};
</script>


<template>
    <div class="parent1">
        <div class="mx-4 mt-4 4k:mt-8">
            <div remove-tailwind-bg>
                <VueGoodTable 
                    :rows="Majors"
                    :columns="columns"
                    v-bind:max-height= tHeight
                    :fixed-header="{
                        enabled: true,
                    }"
                    :search-options="{
                        enabled: true,
                        placeholder: 'Search Majors',
                    }"
                    :pagination-options="{
                        enabled: true,
                        //mode: 'pages',
                        perPage: 30
                    }">
                    <template #table-row="props">
                        <span v-if="props.column.field == 'delete'">
                            <button type="button" class="" v-on:click="deleteClicked(props.row.majorId)">
                                <img src="/images/delete.svg" />
                            </button>
                        </span>
                    </template>
                    <template #emptystate>
                        No entries found!
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

