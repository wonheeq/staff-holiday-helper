import { defineStore } from 'pinia';
import axios from "axios";

export let useStaffStore = defineStore('staffMembers', {
    state: () => ({
        staffMembers: [],
        staffValue: ''
    }),

    actions: {
        async fetchStaffMembers(accountNo){
            axios.get('/api/getStaffMembers/' + accountNo)
            .then((resp) => {
                this.staffMembers.length = 0;
                this.staffMembers = resp.data;
            })
            .catch ((error) => {
                console.log(error)
            });
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
