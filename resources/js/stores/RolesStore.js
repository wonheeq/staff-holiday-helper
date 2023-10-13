import { defineStore } from 'pinia';
import axios from 'axios';

export let useRolesStore = defineStore('roles', {
    state: () => ({
        roles: [],
    }),

    actions: {
        async fetchRolesForStaff(staffNo) {
            axios.get('/api/getRolesForStaffs/' + staffNo)
            .then(resp => {
                this.roles.length = 0;
                this.roles = resp.data;
            })
            .catch (error => {
                console.log(error);
            });
        },
    },

    getters: {

    },

    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
