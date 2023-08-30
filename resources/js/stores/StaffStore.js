import { defineStore } from 'pinia';
import axios from "axios";

export let useStaffStore = defineStore('staffMembers', {
    state: () => ({
        staffMembers: [],
        staffValue: ''
    }),

    actions: {
        // *To do* dynamically added current user account id to replace 000002L
        async fetchStaffMembers(){
            try {
                const resp = await axios.get('/api/getStaffMembers/' + '000002L');
                this.staffMembers = resp.data;
              }
              catch (error) {
                alert(error)
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
    }
});