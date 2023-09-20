import { defineStore } from 'pinia';

export let useRolesStore = defineStore('roles', {
    state: () => ({
        roles: [],
    }),

    actions: {
        async fetchRolesForStaff(staffNo) {
            try {
                const resp = await axios.get('/api/getRolesForStaffs/' + staffNo);
                this.roles = resp.data;
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        },
    },

    getters: {
        
    }
});