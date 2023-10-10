<script setup>
    import { reactive, ref } from 'vue';
    import Modal from '../Modal.vue';
    import axios from "axios";
    import Swal from 'sweetalert2';

    import { useDark } from "@vueuse/core";
    import { storeToRefs } from 'pinia';
    import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
    const screenSizeStore = useScreenSizeStore();
    const { isMobile } = storeToRefs(screenSizeStore);
    const isDark = useDark();
    let emit = defineEmits(['close']);
    var uploadedFile = null;
    const fileInputKey = ref(0);

    let msg = reactive({
        warning: false,
        errorMsg: "default"
    }); 

    let props = defineProps({
        csvFileName: String,
        curTable: String,
        user: String,
        required: true
    });

    /*function closeCSVPopup() {
        emit('close');   
    }*/

    function csvRequested() {
        let file = props.csvFileName;
        //console.log(file);
        // Upload appropriate .csv file to user
        axios.get("/api/getCSVTemplate/" + props.user + "/" + file, { responseType: 'blob'})
        .then((response) => {
            //console.log(response);
            //console.log(response.data);

            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(
                new Blob([response.data])
            );

            link.setAttribute('download', file);

            document.body.appendChild(link);

            link.click();
        })
        .catch((error) => {
            console.log(error);
        });
    }

    function csvSubmitted(event) {
        // Download appropriate .csv file from user
        uploadedFile = event.target.files[0];
        //console.log(uploadedFile);
    }  

    function csvPosted() {
        if (uploadedFile != null) {
            // Convert file to json
            let lines = "";
            let currentLine = "";
            let csv = "";
            let headers = "";
            let jsonEntries = [];
            const reader = new FileReader();
                
            //console.log(uploadedFile);

            reader.readAsBinaryString(uploadedFile);
            reader.onload = (res) => {
                csv = res.target.result;
                //console.log(csv);
                // lines = csv.split("\r" + "\n");
                lines = csv.split("\n");

                headers = lines[1].split(",");
                //console.log(headers.length);
                //console.log(headers);

                var i = 0

                for (var i = 2; i < lines.length; i++) {
                    if (!lines[i]) 
                    continue
                    let obj = {};
                    currentLine = lines[i];
                    //console.log(currentLine);
                    //var re = /"/g;
                    //currentLine = re[Symbol.replace](currentLine,'');
                    currentLine = currentLine.split(",");

                    if (currentLine[0] === "") {
                        break;
                    }

                    var j = 0

                    while (headers[j] != "") {                        
                        let head = headers[j].trim();
                        let value = currentLine[j].trim();
                        obj[head] = value;   
                        j++;                     
                    }
                    //jsonEntries.push(obj);
                    jsonEntries[jsonEntries.length] = obj;
                }
                
                let data = { 
                'table': props.csvFileName,
                'entries': jsonEntries
                }

                // Send .csv file to backend.
                console.log(data);
                axios.post("/api/addEntriesFromCSV/" + props.user, data)
                .then(res => {
                    if (res.status == 200) {
                        Swal.fire({
                            icon: "success",
                            title: res.data.success
                        });
                        
                        msg.warning = false;
                    }
                }).catch(err => {
                    console.log(err)
                    // Something went wrong
                    msg.warning = true;

                    if (err.response.data.error == null)
                    {
                        msg.errorMsg = "The submitted CSV was not valid, ensure you filled in the correct template.";
                    }
                    else 
                    {
                        msg.errorMsg = err.response.data.error;
                    } 
                });

                uploadedFile = null;
                fileInputKey.value += 1;
            };   
        }
        else { 
            
            msg.warning = true;
            msg.errorMsg = "No file chosen";
            console.log(msg.errorMsg);
        }
    }
</script>


<template>
    <Modal>
        <div class="h-screen flex items-center w-screen bg-transparent">
            <div class="mx-4 laptop:w-3/6 flex flex-col p-4 laptop:mx-auto h-4/6 rounded-tl-md overflow-auto rounded-bl-md rounded-br-md laptop:rounded-tr-md"
                :class="isDark?'bg-gray-800':'bg-white'"
            >
                <div class="h-[10%] flex justify-between 4k:ml-6">
                    <slot />
                    <p class="text-xl mr-4 font-bold 4k:text-3xl 4k:mt-6">             
                        Add data to {{ curTable }} table with .csv file:
                    </p>
                    <!--Add full ui then worry about implementation-->
                    <button class="h-full" @click="$emit('close')">
                        <img src="/images/close.svg" class="h-2/3 w-2/3" :class="isDark?'darkModeImage':''"/>
                    </button>
                </div> 
                <div class="mt-6 4k:ml-6">
                    <p class="4k:text-2xl">             
                        Fill in the provided template for the selected table and upload.
                    </p>
                    <button class="mt-6 underline 4k:text-2xl" @click="csvRequested()">    
                        Download CSV Template {{ csvFileName }}    
                    </button>
                    <p class="mt-6 laptop:mt-20 mb-8 4k:text-2xl">    
                        Note: Only .csv files accepted, if the file was converted into another type while being filled in it must first be converted back to .csv type before uploading.
                    </p>
                    <div class="flex flex-col laptop:flex-row">
                        <input type="file" class="laptop:pl-2 py-2 laptop:ml-4 text-center text-lg 4k:text-3xl 4k:pl-5 4k:py-5"
                            :class="isDark?'':'bg-[#e3e3e3]'"
                            @change="csvSubmitted"
                            accept=".csv"
                            :key="fileInputKey">
                    
                        <button class="px-6 py-2 laptop:ml-4 text-center text-lg 4k:text-3xl"
                            :class="isDark?'bg-gray-600':'bg-[#e3e3e3]'"   
                            @click="csvPosted()">
                            Upload .CSV
                        </button>
                    </div>
                    <h4 class="mx-2 laptop:mx-4 mt-6 text-sm 4k:text-2xl 4k:mt-9"
                        v-show="msg.warning"
                        :class="isDark?'text-red-500':'text-red-700'"    
                    >
                        <span v-html="msg.errorMsg" ></span>
                    </h4>
                </div>
            </div>
        </div>
    </Modal>
</template>
    

<style>
</style>
