<script setup>
    
    import SubpageNavbar from '../SubpageNavbar.vue';
    import { onMounted, ref } from 'vue';
    
    const options = [
    { id: 'viewData', title: 'View/Edit Data'},
    { id: 'addData', title: 'Add Data'},
    { id: 'sysSettings', title: 'System Settings'},
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
    const subpageClass = "p-4 rounded-bl-md rounded-br-md rounded-tr-md bg-white h-[95%]";
</script>

<script>

    import table1 from './Table_Accounts.vue';
    import table2 from './Table_Applications.vue';

    export default {
        data: function() {
            return {
                content: '',
                buttons: [
                    { message: 'Staff Accounts' },
                    { message: 'Leave\nApplications' },
                    { message: 'Substitute\nNominations' },
                    { message: 'Account Roles' },
                    { message: 'Roles' },
                    { message: 'Units' },
                    { message: 'Majors' },
                    { message: 'Courses' },
                    { message: 'Schools' },
                    { message: 'Messages' }
                ]
            }
        },
        components:{
            'accountTable':table1,
            'applicationTable':table2
        },
        methods: {
            activate: function(message) {
                this.content = message;
            }
        }
    }
</script>

<template>
    <div class="flex flex-col screen mt-4 mx-4 drop-shadow-md">
        <SubpageNavbar
            class="h-[5%]"
            :options="options"
            :activeScreen="activeScreen"
            @screen-changed="screen => handleActiveScreenChanged(screen) "
        />
        <div
            v-show="activeScreen === 'viewData'"
            :class="subpageClass"
            class="p-4 h-[95%]"
        >

            <h1 class="text-2xl px-4">Database Data:</h1>
            
            <!-- To switch between tables -->
            <div class="flex flex-row mt-4 mx-4">
                <h2 class="mt-1.5">Select Table:</h2>
                <div class="grow grid grid-cols-auto auto-rows-fr gap-3">
                    <button
                        v-for="button in buttons"
                        :key="button.message"
                        class= tableButtonOff
                        :class="{'tableButtonOn': button.message === content}"
                        @click="activate(button.message)"
                    >
                        <span>{{ button.message }}</span>
                    </button>
                </div>
            </div>
            <accountTable>
                
            </accountTable>    
        </div>
        </div>  

        <div
            v-show="activeScreen === 'addData'"
            :class="subpageClass"
        >
            add data subpage
        </div>
        <div
            v-show="activeScreen === 'sysSettings'"
            :class="subpageClass"
        >
            settings subpage
        </div>

<!---->
</template>

<style>

    .tableButtonOn {
        min-width: 13%;
        font-size: 1rem;
        font-weight: bold;
        line-height: 1.3rem;
        text-align: center;
        padding: 2px;
        background-color: rgb(227 227 227);
        border-color: black;
        border-width: 2px;       
        border-style: solid;
        /*w-50 text-1xl text-center p-4 bg-gray-300 */

        margin-left: 1rem;
        /*space-x-4*/
    }

    .tableButtonOff {
        min-width: 13%;
        font-size: 1rem;
        line-height: 1.3rem;
        text-align: center;
        padding: 2px;
        background-color: rgb(227 227 227);
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

</style>