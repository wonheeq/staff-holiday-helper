<script setup>

import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
    
</script>

<script>
    import axios from "axios";
    import { ref } from 'vue'

    export default {
        props: {
            fieldsList: {
                type: [Array, Object],
                required: true
            },
            user: {
                type: String,
                required: true
            }
            /*namesList: {
                type: [Array, Object],
                required: true
            }*/
        },
        data: function() {
            return {
                content: 'Staff Accounts',
                selected: null,
                currentFields: "accountFields",
                buttons: [
                    { message: 'Staff Accounts', fArray: "accountFields"},
                    { message: 'Leave\nApplications', fArray: "applicationFields"},
                    { message: 'Substitute\nNominations', fArray: "nominationFields"},
                    { message: 'Account Roles', fArray: "accountRoleFields"},
                    { message: 'Roles', fArray: "roleFields"},
                    { message: 'Units', fArray: "unitFields"},
                    { message: 'Majors', fArray: "majorFields"},
                    { message: 'Courses', fArray: "courseFields"},
                    { message: 'Schools', fArray: "schoolFields"},
                    { message: 'Messages', fArray: "messageFields"}
                ],
                bHeight: ((0.8889 * window.innerHeight) - 358.2223).toFixed(0) + "px",

                completeFKs: [],
                accounts: [], accountRoles: [], applications: [], roles: [], units: [], majors: [], courses: [], schools: [],

                lmanagers: [],
                acctTypes: [
                    { db_name: 'staff', name: 'Staff'},
                    { db_name: 'lmanager', name: 'Line Manager'},
                    { db_name: 'sysadmin', name: 'System Administor'}
                ],
                applStatus: [
                    { db_name: 'Y', name: 'Approved'},
                    { db_name: 'N', name: 'Denied'},
                    { db_name: 'U', name: 'Undecided'},
                    { db_name: 'P', name: 'Pending'},
                    { db_name: 'C', name: 'Cancelled'}
                ],
                nomStatus: [
                    { db_name: 'Y', name: 'Yes'},
                    { db_name: 'N', name: 'No'},
                    { db_name: 'U', name: 'Undecided'}
                ],

                // An array containing the data entered into the manual input:
                attributeEntries: []    
            }
        },
        methods: {
            activate: function(message, fArray) {
                this.content = message;
                this.currentFields = fArray;
            },
            addToDB: function() {
                //console.log(this.attributeEntries)

                // Now we have an array, it must be sent somewhere (can it be checked for fullness first?)
            },
            getArray(arrayName) {
                return this[arrayName];
            },
            onResize() {
            this.bHeight = ((0.8889 * window.innerHeight) - 358.2223).toFixed(0) + "px"
            //this.tHeight = (window.innerHeight).toFixed(0) + "px"
            //console.warn("tHeight: ", this.tHeight)
            },
        },
        // Using height of window to determine max table height
        mounted() {
            axios.get("/api/allFKData/" + this.user)
            .then((response) => {
                this.completeFKs = response.data;

                this.accounts = this.completeFKs[0];
                this.accountRoles = this.completeFKs[1];
                this.applications = this.completeFKs[2];
                this.roles = this.completeFKs[3];
                this.units = this.completeFKs[4];
                this.majors = this.completeFKs[5];
                this.courses = this.completeFKs[6];
                this.schools = this.completeFKs[7];

                //console.log(response.data);
            })
            .catch((error) => {
                console.log(error);
            });
            axios.get("/api/allLManagers/" + this.user)
            .then((response) => {
                this.lmanagers = response.data;
                //console.log(response.data);
            })
            .catch((error) => {
                console.log(error);
            });
            this.$nextTick(() => {
                window.addEventListener('resize', this.onResize);
                //console.warn("tHeight: ", this.tHeight)
            })
        },
        beforeDestroy() { 
            window.removeEventListener('resize', this.onResize); 
        }
    }
</script>


<template>
    <h1 class="text-2xl px-4">Add Data:</h1>

    <!-- To select table -->
    <div class="flex flex-row mt-4 mx-4">
        <h2 class="mt-1.5">Select Table:</h2>
        <div class="grow grid grid-cols-auto auto-rows-fr gap-3">
            <button
                v-for="button in buttons"
                :key="button.message"
                class= tableButtonOff
                :class="{'tableButtonOn': button.message === content}"
                @click="activate(button.message, button.fArray)"
            >
                <span>{{ button.message }}</span>
            </button>
        </div>
    </div>

    <!-- To import .csv file -->
    <div class="flex flex-row mt-8 mx-4">
        <h1 class="mt-1.5">Add By CSV:</h1>
        <button 
            class= tableButtonOff
        >
            <span> Import .csv </span>
        </button>
    </div>


    <h1 class="mt-1.5 px-4 mt-6">Add Manually:</h1>
    <div class= manualArea :style="{ maxHeight: bHeight }">
        <div class="flex justify-between">
            <div class="flex flex-col mt-4 mx-4 mb-3">
                <!--<div>array: {{ fieldsList.accountFields }}</div>-->
                <!--<div>array: {{ fieldsList[currentFields] }}</div>-->
                <!--v-for="name in namesList[field.fk]""-->
                <div 
                    v-for="(field, index) in fieldsList[currentFields]" :key="index"
                    
                >
                    <div class="flex justify-between space-x-7">
                        <span class="mt-4">{{ field.desc }}: </span>
                        <input v-if="field.fk === 'none'"
                               style="width: 35rem; height: 2rem; margin-top: 0.75rem;" 
                               type="text" autocomplete="off" :placeholder="field.plhldr" 
                               v-model="attributeEntries[index]" />
                        <!--<v-select v-else v-model="selected" style="width: 35rem; height: 2rem; margin-top: 0.75rem;">
                            <option disabled value="" >{{ field.plhldr }}</option>
                            <option v-for="item in schools" :key="item.name" :value="item.name">{{ item.name }}</option>
                        </v-select>-->
                        <form v-else >
                            <vSelect :options="getArray(field.fk)" :label="field.fkAttr" 
                                     style="width: 35rem; height: 2rem; margin-top: 0.75rem; background-color: white; 
                                     border: solid; border-color: #6b7280; border-width: 1px;
                                     --vs-border-style: none; --vs-search-input-placeholder-color: #6b7280"                                 
                                     :placeholder="field.plhldr"
                                     v-model="attributeEntries[index]" >
                            </vSelect>
                        </form>
                    </div>
                </div>
                
            </div>
            <div class="flex self-center">
                <button
                    class="bg-white px-4 py-1 mx-16 mt-1 text-center text-1xl"
                    @click="addToDB()">
                    <span> Add </span>
                </button>
            </div>
        </div>     
    </div>

</template> 


<style>
    .manualArea {
        background-color: rgb(227 227 227);
        overflow: scroll; 
        margin-left: 1rem;
        margin-right: 1rem;
        margin-top: 0.5rem;
        height: 100%;
    }

    
</style>