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
            axios.get('/api/getRolesForStaffs/' + accountNo)
            .then((resp) => {
                this.staffRoles.length = 0;
                this.staffRoles = resp.data;
            })
            .catch((error) => {
                console.log(error);
            });
            axios.get('/api/getSpecificStaffMember/' + accountNo)
            .then((resp) => {
                this.staffInfo.length = 0;
                this.staffInfo = resp.data;
            })
            .catch((error) => {
                console.log(error);
            });
        },
        async fetchAllUnits(){

            axios.get('/api/getUCM/')
            .then((resp) => {
                this.allUnits.length = 0;
                this.allUnits = resp.data;
            })
            .catch((error) => {
                console.log(error);
            });
        }
    },
    getters: {

    },

    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
