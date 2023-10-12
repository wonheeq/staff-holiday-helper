<script setup>
import { ref } from 'vue';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { useDark } from "@vueuse/core";
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const isDark = useDark();

const buttons = [
    { label: 'Accounts', fArray: "accountFields", csvFileName: "add_staffaccounts.csv"},  
    { label: 'Account Roles', fArray: "accountRoleFields", csvFileName: "add_accountroles.csv"},
    { label: 'Roles', fArray: "roleFields", csvFileName: "add_roles.csv"},
    { label: 'Units', fArray: "unitFields", csvFileName: "add_units.csv"},
    { label: 'Majors', fArray: "majorFields", csvFileName: "add_majors.csv"},
    { label: 'Courses', fArray: "courseFields", csvFileName: "add_courses.csv"},
    { label: 'Schools', fArray: "schoolFields", csvFileName: "add_schools.csv"}            
];
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
            let defaultC = 288
            return {
                content: 'Accounts',
                selected: null,
                currentFields: "accountFields",
                currentCSV: "add_staffaccounts.csv",
                c: defaultC,
                bHeight: ((0.8889 * window.innerHeight) - defaultC).toFixed(0) + "px",

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
                errorMsg: '',

                fontSizeDrpDwn: '1rem'
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
                //console.log(this.attributeEntries)
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
            this.bHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
            //this.tHeight = (window.innerHeight).toFixed(0) + "px"
            ////console.warn("tHeight: ", this.tHeight)
            },
        },
        created() { 
            if (screen.width >= 3840) {
                this.fontSizeDrpDwn = '1.5rem';
            }
            if (screen.width < 1430 && screen.width >= 1350)
            {
                this.c = 315;
                this.bHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
            }
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
                //console.log(response.data);
                //console.log(response.data);

                // lmanagers is a nullable field, adding "none" option
                var nullObject = {accountNo: null, fullName: 'None'}
                this.lmanagers.unshift(nullObject);
            })
            .catch((error) => {
                console.log(error);
            });
            this.$nextTick(() => {
                window.addEventListener('resize', this.onResize);
                ////console.warn("tHeight: ", this.tHeight)
            })
        },
        beforeDestroy() { 
            window.removeEventListener('resize', this.onResize); 
        }
    }
</script>


<template>
    <h1 class="text-2xl laptop:px-4 4k:text-5xl 4k:py-4">Add Data:</h1>

    <!-- To select table -->
    <div v-if="isMobile">
        <vSelect
            :clearable="false"
            :searchable="false"
            :filterable="false"
            :class="isDark?'dropdown-dark':''"
            :options="buttons"
            placeholder="Accounts"
            @option:selected="(selectedOption) => {activate(selectedOption.label, selectedOption.fArray, selectedOption.csvFileName)}"
        >
        </vSelect>
    </div>
    <div v-else class="flex flex-row mt-4 mx-4">
        <h2 class="mt-1.5 4k:text-3xl 4k:mt-3">Select Table:</h2>
        <div class="grow grid grid-cols-auto auto-rows-fr gap-3">
            <button
                v-for="button in buttons"
                :key="button.label"
                :class="{
                    'tableButtonOn': button.label === content && !isDark,
                    'tableButtonOnDark': button.label === content && isDark,
                    'tableButtonOff': button.label != content && !isDark,
                    'tableButtonOffDark': button.label != content && isDark,
                }"
                @click="activate(button.label, button.fArray, button.csvFileName)"
            >
                <span>{{ button.label }}</span>
            </button>
        </div>
    </div>

    <!-- To import .csv file -->
    <div class="flex flex-row mt-4 laptop:mt-8 mx-4 4k:mt-10">
        <h2 class="mt-1.5 4k:text-3xl 4k:mt-3">Add By CSV:</h2>
        <button
            :class="isDark?'tableButtonOffDark':'tableButtonOff'"
            @click="activateCSV()"
        >
            <span> Import .csv </span>
        </button>
    </div>


    <h1 class="mt-1.5 mx-4 mt-3 laptop:mt-6 4k:text-3xl 4k:mt-10">Add Manually:</h1>
    <div :class="isMobile?(isDark?'manualAreaDarkMobile':'manualAreaMobile'):(isDark?'manualAreaDark':'manualArea')"
        :style="{ maxHeight: bHeight }"
    >
        <div class="flex justify-between">
            <div class="flex flex-col laptop:mt-4 mx-2 laptop:mx-4 laptop:mb-3 w-fit">
                <!--<div>array: {{ fieldsList.accountFields }}</div>-->
                <!--<div>array: {{ fieldsList[currentFields] }}</div>-->
                <!--v-for="name in namesList[field.fk]""-->
                <div 
                    v-for="(field, index) in fieldsList[currentFields]" :key="index"
                    class="w-full"
                >
                    <div class="flex flex-row justify-between laptop:space-x-7 4k:space-x-11 w-full">
                        <span v-if="isMobile" class="w-[6.5rem] mt-4">{{ field.desc }}: </span>
                        <span v-else class="mt-4 4k:mt-10 1080:text-lg 1440:text-xl 4k:text-2xl">{{ field.desc }}: </span>
                        <input v-if="field.fk === 'none'"
                               class="input_options" 
                               :class="isMobile?(isDark?'bg-gray-800 border-white text-white w-[14rem]':'w-[14rem]'):(isDark?'bg-gray-800 border-white text-white w-[35rem]':'w-[35rem]')"
                               type="text" autocomplete="off" :placeholder="field.plhldr" 
                               v-model="attributeEntries[index]" />
                        <!--<v-select v-else v-model="selected" style="width: 35rem; height: 2rem; margin-top: 0.75rem;">
                            <option disabled value="" >{{ field.plhldr }}</option>
                            <option v-for="item in schools" :key="item.name" :value="item.name">{{ item.name }}</option>
                        </v-select>-->
                        <form id="addDataForm" autocomplete="off" v-else >
                            <vSelect :options="getArray(field.fk)" :label="field.fkAttr" 
                                :class="isDark ? 'dropdown-dark':''"
                                class="input_options"
                                :style="isMobile?'width: 14rem; font-size: 0.75rem;':'width: 35rem; font-size: 1rem;'"                     
                                :placeholder="field.plhldr"
                                v-model="attributeEntries[index]" >
                            </vSelect>
                        </form>
                    </div>
                </div>
                
            </div>        
               
        </div><!--<div class="flex flex-col self-center">-->
        <div v-if="isMobile" class="w-full p-2">
            <button
                class="w-full py-2 text-center text-xl font-bold"
                :class="isDark?'bg-gray-800':'bg-white'"
                @click="addToDB()">
                <span> Add </span>       
            </button>
            <h4 class="w-full mx-2 mt-1.5 text-center text-sm text-red-500" v-show="warning">
                <span v-html="errorMsg"></span>
            </h4>
        </div>    
        <div v-else class="centeredRight">
            <button
                class="px-6 py-2 mx-28 text-center text-xl 4k:text-2xl font-bold 4k:text-4xl 4k:px-9 4k:py-4"
                :class="isDark?'bg-gray-800':'bg-white'"
                @click="addToDB()">
                <span> Add </span>       
            </button>
            <h4 class="mx-4 mt-3 text-center text-sm 1440:text-base text-red-700" v-show="warning">
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
        margin-top: 0.5rem;
        height: 80%;
        position: relative;
    }

    .manualAreaMobile {
        background-color: rgb(227 227 227);
        overflow: scroll; 
        margin-top: 0.5rem;
        height: 80%;
        width: 100%;
        position: relative;
    }

    .manualAreaDark {
        background-color: #324057;
        overflow: scroll; 
        margin-left: 1rem;
        margin-top: 0.5rem;
        height: 80%;
        position: relative;
    }

    .manualAreaDarkMobile {
        background-color: #324057;
        overflow: scroll; 
        margin-top: 0.5rem;
        height: 80%;
        width: 100%;
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

    .vs__search, .vs__search:focus {
        font-size: v-bind(fontSizeDrpDwn);
    }
</style>

<style lang="postcss">

.input_options {
    height: 2rem; 
    margin-top: 0.75rem;
    @apply 1080:text-lg 1440:text-xl 4k:text-2xl 4k:h-11 4k:w-drpdwn 4k:mt-9 !important;
}

</style>