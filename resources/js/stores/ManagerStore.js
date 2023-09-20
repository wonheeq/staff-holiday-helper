import { defineStore } from 'pinia';

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
                alert("Failed to load data: Please try again");
                console.log(error);
            }
        },
        async fetchAllUnits(){
            try{
                const resp = await axios.get('/api/getUCM/')
                this.allUnits = resp.data;
            } catch (error){
                alert("Failed to load data: Please try again");
                console.log(error);
            }
        }
    },
    getters: {
        
    }
});