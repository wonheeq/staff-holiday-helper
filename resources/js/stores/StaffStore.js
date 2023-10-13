import { defineStore } from 'pinia';
import axios from "axios";

export let useStaffStore = defineStore('staffMembers', {
    state: () => ({
        staffMembers: [],
        staffValue: ''
    }),

    actions: {
        // *To do* dynamically added current user account id to replace 000002L
        async fetchStaffMembers(accountNo){
            try {
                const resp = await axios.get('/api/getStaffMembers/' + accountNo);
                this.staffMembers = resp.data;
              }
              catch (error) {
                console.log(error)
            }
        }
    },

    getters: {
        searchStaff() {
            return this.staffMembers.filter(staff =>
                ((staff.fName + " " + staff.lName).toLowerCase().includes(this.staffValue.toLowerCase())) ||
                (staff.accountNo.includes(this.staffValue))
            );
        }
    },

    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
