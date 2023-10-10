<script setup>
    
    import SubpageNavbar from '../SubpageNavbar.vue';
    import { ref, computed } from 'vue';
    import AddDataPage from './AddData.vue';

    import vSelect from "vue-select";
    import "vue-select/dist/vue-select.css";
    import { useDataFieldsStore } from '@/stores/AddDataStore';
    import SystemSettings from './SystemSettings.vue';

    import { usePage } from '@inertiajs/vue3';

    import { useDark } from "@vueuse/core";
    import { storeToRefs } from 'pinia';
    import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
    const screenSizeStore = useScreenSizeStore();
    const { isMobile } = storeToRefs(screenSizeStore);
    const isDark = useDark();
    const page = usePage();
    const user = computed(() => page.props.auth.user);

    // Store of fields needed to create new database entry
    let fieldsStore = useDataFieldsStore();
    
    const options = [
    { id: 'viewData', title: 'View/Edit Data', mobileTitle: 'View/Edit Data'},
    { id: 'addData', title: 'Add Data', mobileTitle: 'Add Data'},
    { id: 'sysSettings', title: 'System Settings', mobileTitle: 'System Settings'},
    ];

    let activeScreen = ref("viewData");


    let props = defineProps({
        screenProp: {
            type: String,
            default: 'default'
        }
    });

    if (props.screenProp !== "default") {
        activeScreen.value = props.screenProp;
    }

    const buttons = [
        { label: 'Accounts', table: 'accountTable' },
        { label: 'Applications', table: 'applicationTable' },
        { label: 'Nominations', table: 'nominationTable' },
        { label: 'Account Roles', table: 'accountRolesTable' },
        { label: 'Roles', table: 'rolesTable' },
        { label: 'Units', table: 'unitsTable' },
        { label: 'Majors', table: 'majorsTable' },
        { label: 'Courses', table: 'coursesTable' },
        { label: 'Schools', table: 'schoolsTable' },
        { label: 'Messages', table: 'messagesTable' }
    ];

    function changeUrl(params) {
        var baseUrl = window.location.origin;

        history.pushState(
            null,
            'LeaveOnTime',
            baseUrl + "/admin/" + params
        );
    }

    function handleActiveScreenChanged(screen) {
        activeScreen.value = screen;
        changeUrl(screen);
    }
</script>

<script>
    import AddCSVData from "@/Components/Admin/AddCSVData.vue";
    import EditDataPage from './EditData.vue';

    import table1 from './Table_Accounts.vue';
    import table2 from './Table_Applications.vue';
    import table3 from './Table_Nominations.vue';
    import table4 from './Table_AccountRoles.vue';
    import table5 from './Table_Roles.vue';
    import table6 from './Table_Units.vue';
    import table7 from './Table_Majors.vue';
    import table8 from './Table_Courses.vue';
    import table9 from './Table_Schools.vue';
    import table10 from './Table_Messages.vue';

    export default {
        data: function() {
            return {
                currentTable: 'accountTable',
                content: 'Accounts',

                csvActivated: false,
                csvFileName: "",
                curTable: "",

                editing: false,
                entryData: null,

                fontSizeMain: '16px',
                fontSize: "14px",
                searchPadding: '6px 12px',
                searchHeight: '32px',
                magnifyingGlassTop: '3px',
                magnifyingGlassLeft: '8px',
                magnifyingGlassWH: '16px',
                sortButtonBorder: '5px',
                sortButtonMargin: '-7px',
                pageChangeBorder: '6px',
                pageChangeMargin: '-6px',
                footerFontSize: '1.1rem',
                pageDropdownRight: '6px',
                pageDropdownMargin: '-1px'
            }
        },
        components:{
            'accountTable':table1,
            'applicationTable':table2,
            'nominationTable':table3,
            'accountRolesTable':table4,
            'rolesTable':table5,
            'unitsTable':table6,
            'majorsTable':table7,
            'coursesTable':table8,
            'schoolsTable':table9,
            'messagesTable':table10
        },
        methods: {
            activate: function(message, table) {
                this.content = message;
                this.currentTable = table;
            },
            activateCSV: function(csvFileName, tableName) {
                //console.log(tableName);
                this.csvFileName = csvFileName;
                this.curTable = tableName;
                this.csvActivated = !this.csvActivated;
            },
            activateEditing: function(entryData) {
                //console.log(params.row);
                this.entryData = entryData;
                
                this.editing = !this.editing;
            },
            activateCSV: function(csvFileName, tableName) {
                //console.log(tableName);
                this.csvFileName = csvFileName;
                this.curTable = tableName;
                this.csvActivated = !this.csvActivated;
            },
            activateEditing: function(entryData) {
                //console.log(params.row);
                this.entryData = entryData;
                
                this.editing = !this.editing;
            }
        },
        created() { 
            if (screen.width >= 3840) {
                this.fontSize = this.fontSizeMain = this.footerFontSize = '1.8rem';
                this.searchPadding = '12px 18px';
                this.searchHeight = '54px';
                this.magnifyingGlassTop = '7px';
                this.magnifyingGlassLeft = '5px';
                this.magnifyingGlassWH = '25px';
                this.sortButtonBorder = '8px';
                this.sortButtonMargin = '-13px';
                this.pageChangeBorder = '10px';
                this.pageChangeMargin = '-10px';
                this.pageDropdownRight = '-4px';
                this.pageDropdownMargin = '-3px';
            }
        },
        mounted() {
            this.$nextTick(() => {
                window.addEventListener('resize', this.onResize);
                ////console.warn("tHeight: ", this.tHeight)
            })
        },
        created() { 
            if (screen.width >= 3840) {
                this.fontSize = this.fontSizeMain = this.footerFontSize = '1.8rem';
                this.searchPadding = '12px 18px';
                this.searchHeight = '54px';
                this.magnifyingGlassTop = '7px';
                this.magnifyingGlassLeft = '5px';
                this.magnifyingGlassWH = '25px';
                this.sortButtonBorder = '8px';
                this.sortButtonMargin = '-13px';
                this.pageChangeBorder = '10px';
                this.pageChangeMargin = '-10px';
                this.pageDropdownRight = '-4px';
                this.pageDropdownMargin = '-3px';
            }
        },
        mounted() {
        
        this.$nextTick(() => {
            window.addEventListener('resize', this.onResize);
            ////console.warn("tHeight: ", this.tHeight)
        })
    },
    }
</script>

<template>
    <div v-if="isMobile" class="flex flex-col screen-mobile mt-2 mx-2 laptop:mt-4 laptop:mx-4 drop-shadow-md">
        <SubpageNavbar
            class="h-[5%]"
            :options="options"
            :activeScreen="activeScreen"
            @screen-changed="screen => handleActiveScreenChanged(screen) "
        />
        <div
            v-show="activeScreen === 'viewData'"
            :class="{
                'bg-gray-800': isDark,
                'bg-white': !isDark,
            }"
            class="p-2 laptop:p-4 rounded-bl-md rounded-br-md laptop:rounded-tr-md h-[95%]"
        >

            <h1 class="text-2xl laptop:px-4 4k:text-5xl 4k:py-4">Database Data:</h1>
            
            <!-- To switch between tables -->
            <div v-if="isMobile">
                <vSelect
                    :clearable="false"
                    :searchable="false"
                    :filterable="false"
                    :class="isDark?'dropdown-dark':''"
                    :options="buttons"
                    placeholder="Accounts"
                    @option:selected="(selectedOption) => {activate(selectedOption.label, selectedOption.table)}"
                >
                </vSelect>
            </div>
            <div v-else class="flex flex-row mt-2 mx-2 laptop:mt-4 laptop:mx-4">
                <h2 class="mt-1.5 4k:text-3xl 4k:mt-6">Select Table:</h2>
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
                        @click="activate(button.label, button.table)"
                    >
                        <span>{{ button.label }}</span>
                    </button>
                </div>
            </div>
            <component :is="currentTable" :user="user.accountNo" @toggleEditing="activateEditing"></component>   
        </div>

        <div
            v-show="activeScreen === 'addData'"
            :class="isDark?'bg-gray-800':'bg-white'"
            class="p-2 laptop:p-4 rounded-bl-md rounded-br-md laptop:rounded-tr-md h-[95%]"
        >
            <!--<AddDataPage :fieldsList="fieldsStore" :namesList="namesStore"/>-->
            <AddDataPage :fieldsList="fieldsStore" :user="user.accountNo" @toggleCSV="activateCSV" />
        </div>
        <SystemSettings v-show="activeScreen === 'sysSettings'"
            :class="isDark?'bg-gray-800':'bg-white'"
            class="p-2 laptop:p-4 rounded-bl-md rounded-br-md laptop:rounded-tr-md h-[95%]"
        />
<!---->

    </div>
    <div v-else class="flex flex-col screen mt-2 mx-2 laptop:mt-4 laptop:mx-4 drop-shadow-md">
        <SubpageNavbar
            class="h-[5%]"
            :options="options"
            :activeScreen="activeScreen"
            @screen-changed="screen => handleActiveScreenChanged(screen) "
        />
        <div
            v-show="activeScreen === 'viewData'"
            :class="{
                'bg-gray-800': isDark,
                'bg-white': !isDark,
            }"
            class="p-2 laptop:p-4 rounded-bl-md rounded-br-md laptop:rounded-tr-md h-[95%]"
        >

            <h1 class="text-2xl px-2 laptop:px-4 4k:text-5xl 4k:py-4">Database Data:</h1>
            
            <!-- To switch between tables -->
            <div class="flex flex-row mt-2 mx-2 laptop:mt-4 laptop:mx-4">
                <h2 class="mt-1.5 4k:text-3xl 4k:mt-6">Select Table:</h2>
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
                        @click="activate(button.label, button.table)"
                    >
                        <span>{{ button.label }}</span>
                    </button>
                </div>
            </div>
            <component :is="currentTable" :user="user.accountNo" @toggleEditing="activateEditing"></component>   
        </div>

        <div
            v-show="activeScreen === 'addData'"
            :class="isDark?'bg-gray-800':'bg-white'"
            class="p-2 laptop:p-4 rounded-bl-md rounded-br-md laptop:rounded-tr-md h-[95%]"
        >
            <!--<AddDataPage :fieldsList="fieldsStore" :namesList="namesStore"/>-->
            <AddDataPage :fieldsList="fieldsStore" :user="user.accountNo" @toggleCSV="activateCSV" />
        </div>
        <SystemSettings v-show="activeScreen === 'sysSettings'"
            :class="isDark?'bg-gray-800':'bg-white'"
            class="p-2 laptop:p-4 rounded-bl-md rounded-br-md laptop:rounded-tr-md h-[95%]"
        />
<!---->

    </div>
    <AddCSVData v-if="csvActivated" :csvFileName="csvFileName" :curTable="curTable" :user="user.accountNo" @close="activateCSV()">
    </AddCSVData>
    <EditDataPage v-if="editing" :table="content" :entry="entryData" :user="user.accountNo" @close="activateEditing">
    </EditDataPage>
</template>

<style lang="postcss">
.screen-mobile {
    /* mobile screen uses 0.5rem for margins */
    height: calc(93vh - 1.5rem);
}
    .tableButtonOn {
        min-width: 13%;
        font-size: 1rem;
        @apply 4k:text-3xl !important;
        @apply 4k:text-3xl !important;
        font-weight: bold;
        line-height: 1.3rem;
        text-align: center;
        padding: 2px;
        padding-left: 1rem;
        padding-right: 1rem;
        @apply 4k:p-3 !important;
        background-color: rgb(227 227 227);
        border-color: black;
        border-width: 2px;       
        border-style: solid;
        /*w-50 text-1xl text-center p-4 bg-gray-300 */

        margin-left: 1rem;
        /*space-x-4*/
    }

    .tableButtonOnDark {
        min-width: 13%;
        font-size: 1rem;
        @apply 4k:text-3xl !important;
        font-weight: bold;
        line-height: 1.3rem;
        text-align: center;
        padding: 2px;
        padding-left: 1rem;
        padding-right: 1rem;
        @apply 4k:p-3 !important;
        background-color: rgb(71, 79, 90);
        border-color: rgb(31 41 55);
        border-width: 2px;       
        border-style: solid;
        /*w-50 text-1xl text-center p-4 bg-gray-300 */

        margin-left: 1rem;
        /*space-x-4*/
    }

    .tableButtonOff {
        min-width: 13%;
        font-size: 1rem;
        @apply 4k:text-3xl !important;
        @apply 4k:text-3xl !important;
        line-height: 1.3rem;
        text-align: center;
        padding: 2px;
        padding-left: 1rem;
        padding-right: 1rem;
        @apply 4k:p-3 !important;
        background-color: rgb(227 227 227);
        /*w-50 text-1xl text-center p-4 bg-gray-300 */

        margin-left: 1rem;
        /*space-x-4*/
    }

    .tableButtonOffDark {
        min-width: 13%;
        font-size: 1rem;
        @apply 4k:text-3xl !important;
        line-height: 1.3rem;
        text-align: center;
        padding: 2px;
        padding-left: 1rem;
        padding-right: 1rem;
        @apply 4k:p-3 !important;
        background-color: rgb(52, 58, 62);
        /*w-50 text-1xl text-center p-4 bg-gray-300 */

        margin-left: 1rem;
        /*space-x-4*/
    }

    .screen {
        height: calc(93vh - 3rem);
    }

    body {
        font-family: "PT Sans", sans-serif;
    }

    table.vgt-table {
        font-size: v-bind(fontSizeMain);
    }

    .vgt-input, .vgt-select {
        font-size: v-bind(fontSize);
        padding:  v-bind(searchPadding);
        height: v-bind(searchHeight);
    }

    .vgt-global-search__input .input__icon .magnifying-glass {
        margin-top: v-bind(magnifyingGlassTop);
        margin-left: v-bind(magnifyingGlassLeft);
        width: v-bind(magnifyingGlassWH);
        height: v-bind(magnifyingGlassWH);
    }

    .vgt-table th.sortable button:before {
        margin-bottom: v-bind(sortButtonMargin);
        border-left: v-bind(sortButtonBorder) solid transparent;
        border-right: v-bind(sortButtonBorder) solid transparent;
        border-top: v-bind(sortButtonBorder) solid #606266;
    }
    .vgt-table th.sortable button:after {
        margin-top: v-bind(sortButtonMargin);
        border-left: v-bind(sortButtonBorder) solid transparent;
        border-right: v-bind(sortButtonBorder) solid transparent;
        border-bottom: v-bind(sortButtonBorder) solid #606266;
    }

    .vgt-table thead th.sorting-asc button:after {
        border-bottom: v-bind(sortButtonBorder) solid #409eff;
    }
    .vgt-table thead th.sorting-desc button:before {
        border-top: v-bind(sortButtonBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__row-count__label,
    .vgt-wrap__footer .footer__row-count__select,
    .vgt-wrap__footer .footer__navigation,
    .vgt-wrap__footer .footer__navigation__page-btn span {
        font-size: v-bind(footerFontSize);
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron.left::after {
        border-right: v-bind(pageChangeBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron.right::after {
        border-left: v-bind(pageChangeBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron:after {
        margin-top: v-bind(pageChangeMargin);
        border-top: v-bind(pageChangeBorder) solid transparent;
        border-bottom: v-bind(pageChangeBorder) solid transparent;
    }

    .vgt-wrap__footer .footer__row-count::after {
        right: v-bind(pageDropdownRight);
        margin-top: v-bind(pageDropdownMargin);
        border-top: v-bind(pageChangeBorder) solid #606266;
        border-left: v-bind(pageChangeBorder) solid transparent;
        border-right: v-bind(pageChangeBorder) solid transparent;
    }

    table.vgt-table {
        font-size: v-bind(fontSizeMain);
    }

    .vgt-input, .vgt-select {
        font-size: v-bind(fontSize);
        padding:  v-bind(searchPadding);
        height: v-bind(searchHeight);
    }

    .vgt-global-search__input .input__icon .magnifying-glass {
        margin-top: v-bind(magnifyingGlassTop);
        margin-left: v-bind(magnifyingGlassLeft);
        width: v-bind(magnifyingGlassWH);
        height: v-bind(magnifyingGlassWH);
    }

    .vgt-table th.sortable button:before {
        margin-bottom: v-bind(sortButtonMargin);
        border-left: v-bind(sortButtonBorder) solid transparent;
        border-right: v-bind(sortButtonBorder) solid transparent;
        border-top: v-bind(sortButtonBorder) solid #606266;
    }
    .vgt-table th.sortable button:after {
        margin-top: v-bind(sortButtonMargin);
        border-left: v-bind(sortButtonBorder) solid transparent;
        border-right: v-bind(sortButtonBorder) solid transparent;
        border-bottom: v-bind(sortButtonBorder) solid #606266;
    }

    .vgt-table thead th.sorting-asc button:after {
        border-bottom: v-bind(sortButtonBorder) solid #409eff;
    }
    .vgt-table thead th.sorting-desc button:before {
        border-top: v-bind(sortButtonBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__row-count__label,
    .vgt-wrap__footer .footer__row-count__select,
    .vgt-wrap__footer .footer__navigation,
    .vgt-wrap__footer .footer__navigation__page-btn span {
        font-size: v-bind(footerFontSize);
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron.left::after {
        border-right: v-bind(pageChangeBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron.right::after {
        border-left: v-bind(pageChangeBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron:after {
        margin-top: v-bind(pageChangeMargin);
        border-top: v-bind(pageChangeBorder) solid transparent;
        border-bottom: v-bind(pageChangeBorder) solid transparent;
    }

    .vgt-wrap__footer .footer__row-count::after {
        right: v-bind(pageDropdownRight);
        margin-top: v-bind(pageDropdownMargin);
        border-top: v-bind(pageChangeBorder) solid #606266;
        border-left: v-bind(pageChangeBorder) solid transparent;
        border-right: v-bind(pageChangeBorder) solid transparent;
    }

</style>