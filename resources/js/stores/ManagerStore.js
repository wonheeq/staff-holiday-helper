import { defineStore } from 'pinia';
import axios from 'axios';

export let useManagerStore = defineStore('manager', {
    state: () => ({
        staffRoles: [],
        staffInfo: [],
        allUnits: []
    }),

    actions: {
        async fetchRolesForStaff(accountNo) {
            try {
                const resp = await axios.get('/api/getRolesForStaffs/' + accountNo);
                const resp2 = await axios.get('/api/getSpecificStaffMember/' + accountNo);
                this.staffRoles = resp.data;
                this.staffInfo = resp2.data;
                console.log(resp2.data);
            } catch (error) {
                console.log(error);
            }
        },
        async fetchAllUnits(){
            try{
                const resp = await axios.get('/api/getUCM/')
                this.allUnits = resp.data;
            } catch (error){
                console.log(error);
            }
        }
    },
    getters: {

    },

    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
