<script setup>

import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
    
</script>

<script>
    import axios from "axios";
    import Swal from 'sweetalert2';

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
        },
        data: function() {
            return {
                content: 'Staff Accounts',
                selected: null,
                currentFields: "accountFields",
                currentCSV: "add_staffaccounts.csv",
                buttons: [
                    { message: 'Staff Accounts', fArray: "accountFields", csvFileName: "add_staffaccounts.csv"},  
                    { message: 'Account Roles', fArray: "accountRoleFields", csvFileName: "add_accountroles.csv"},
                    { message: 'Roles', fArray: "roleFields", csvFileName: "add_roles.csv"},
                    { message: 'Units', fArray: "unitFields", csvFileName: "add_units.csv"},
                    { message: 'Majors', fArray: "majorFields", csvFileName: "add_majors.csv"},
                    { message: 'Courses', fArray: "courseFields", csvFileName: "add_courses.csv"},
                    { message: 'Schools', fArray: "schoolFields", csvFileName: "add_schools.csv"}            
                ],
                bHeight: ((0.8889 * window.innerHeight) - 288.2223).toFixed(0) + "px",

                completeFKs: [],
                roles: [], units: [], majors: [], courses: [], schools: [],

                lmanagers: [], displayAccounts: [],
                acctTypes: [
                    { db_name: 'staff', name: 'Staff'},
                    { db_name: 'lmanager', name: 'Line Manager'},
                    { db_name: 'sysadmin', name: 'System Administor'}
                ],
                /*applStatus: [
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
                ],*/

                // An array containing the data entered into the manual input:
                attributeEntries: [],
                warning: false,
                errorMsg: ''
            }
        },
        methods: {
            activate: function(message, fArray, csvFileName) {
                this.content = message;
                this.currentFields = fArray;
                this.currentCSV = csvFileName;

                // Clear Fields if needed
                this.attributeEntries = [];   
            },
            activateCSV: function() {
                this.$emit('toggleCSV', this.currentCSV, this.content);      
            },
            // Add single entry to selected table
            addToDB: function() {
                console.log(this.attributeEntries)
                this.warning = false;

                // Checking array is at least populated
                if (this.attributeEntries.length === this.fieldsList[this.currentFields].length) {
                    // Checking that none of the entries are null
                    for (let i = 0; i < this.fieldsList[this.currentFields].length; i++) {
                        if (this.attributeEntries[i] == null || this.attributeEntries[i] === "") {
                            this.errorMsg = 'One or more fields<br />are missing';
                            this.warning = true;
                        }
                    }
                }
                else {
                    // Warning message
                    this.errorMsg = 'One or more fields<br />are missing';
                    this.warning = true;
                }

                // If all fields are filled, send the array
                if (this.warning === false) { 
                    // Sending array plus 'currentFields' name so controller can work out the intended relation or the new entry
                    let data = {
                        'fields': this.currentFields,
                        'newEntry': this.attributeEntries
                    }

                    axios.post("/api/addSingleEntry/" + this.user, data)
                    .then(res => {
                        if (res.status == 200) {
                            Swal.fire({
                                icon: "success",
                                title: 'Successfully added entry.'
                            });

                            // Clear Fields
                            this.attributeEntries = [];     
                        }
                    }).catch(err => {
                        console.log(err)
                        // Something went wrond
                        // Add message below 'add' button
                        this.warning = true;
                        this.errorMsg = err.response.data.error;
                    });
                }

            },
            getArray(arrayName) {
                return this[arrayName];
            },
            onResize() {
            this.bHeight = ((0.8889 * window.innerHeight) - 288.2223).toFixed(0) + "px"
            //this.tHeight = (window.innerHeight).toFixed(0) + "px"
            //console.warn("tHeight: ", this.tHeight)
            },
        },
        // Using height of window to determine max table height
        mounted() {
            axios.get("/api/allFKData/" + this.user)
            .then((response) => {
                this.completeFKs = response.data;

                //this.accounts = this.completeFKs[0];
                //this.accountRoles = this.completeFKs[1];
                //this.applications = this.completeFKs[2];
                this.roles = this.completeFKs[0];
                this.units = this.nullUnits = this.completeFKs[1];
                this.majors = this.nullMajors = this.completeFKs[2];
                this.courses = this.nullCourses = this.completeFKs[3];
                this.schools = this.completeFKs[4];

                //console.log(response.data);
                // In the 'accountRole' table some of the fields are nullable, and so require a 'none' option
                this.units.unshift({unitId: null, disName: 'None'});
                this.majors.unshift({majorId: null, disName: 'None'});
                this.courses.unshift({courseId: null, disName: 'None'});
            })
            .catch((error) => {
                console.log(error);
            });
            axios.get("/api/allAccountsDisplay/" + this.user)
            .then((response) => {
                var resposeArr = response.data;
                this.lmanagers = resposeArr[0];
                this.displayAccounts = resposeArr[1];
                console.log(response.data);

                // lmanagers is a nullable field, adding "none" option
                var nullObject = {accountNo: null, fullName: 'None'}
                this.lmanagers.unshift(nullObject);
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
                @click="activate(button.message, button.fArray, button.csvFileName)"
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
            @click="activateCSV()"
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
                        <input  v-if="field.fk === 'none'"
                               style="width: 35rem; height: 2rem; margin-top: 0.75rem;" 
                               type="text" autocomplete="off" :placeholder="field.plhldr" 
                               v-model="attributeEntries[index]" />
                        <!--<v-select v-else v-model="selected" style="width: 35rem; height: 2rem; margin-top: 0.75rem;">
                            <option disabled value="" >{{ field.plhldr }}</option>
                            <option v-for="item in schools" :key="item.name" :value="item.name">{{ item.name }}</option>
                        </v-select>-->
                        <form  autocomplete="off" v-else >
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
               
        </div><!--<div class="flex flex-col self-center">-->
            <div class="centeredRight">
                <button
                    class="bg-white px-6 py-2 mx-28 text-center text-xl font-bold"
                    @click="addToDB()">
                    <span> Add </span>       
                </button>
                <h4 class="mx-4 mt-3 text-center text-sm text-red-700" v-show="warning">
                    <span v-html="errorMsg"></span>
                </h4>
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
        position: relative;
    }

    .centeredRight {
        text-align: center;
        position: absolute;
        top: 50%;
        left: 75%;
        right: 0;
        margin: auto;
        transform: translateY(-50%);
    }

    .vs__selected-options {
        flex-wrap: nowrap;
        max-width: calc(100% - 40px);
    }

    .vs__selected {
        display: block;
        white-space: nowrap;
        text-overflow: ellipsis;
        max-width: 100%;
        overflow: hidden;
    }

    
</style>