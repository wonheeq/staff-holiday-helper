import { defineStore } from 'pinia';
import { useUserStore } from './UserStore';
import axios from "axios";

export let useApplicationStore = defineStore('applications', {
    state: () => ({
        applications: [],
        managerApplications: [],
        viewing: 'all'
    }),

    actions: {
        async fetchApplications() {
            try {
                const resp = await axios.get('/api/applications/' + useUserStore().userId);
                this.applications = resp.data;
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        },
        // To do, dynamically added current user account id to replace 0000002L
        async fetchManagerApplications(){
            try {
                const resp = await axios.get('/api/managerApplications/' + '000002L');
                this.managerApplications = resp.data;
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        }
    },
    getters: {
        filteredApplications(){
            if(this.viewing === 'unAcknowledged'){
                return this.managerApplications.filter(application => application.status === 'U');
            }
            else if(this.viewing === 'accepted')
            {
                return this.managerApplications.filter(application => application.status === 'Y');
            }
            else if(this.viewing === 'rejected')
            {
                return this.managerApplications.filter(application => application.status ==='N');
            }
            else
            {
                return this.managerApplications;
            }
        },
    }
});