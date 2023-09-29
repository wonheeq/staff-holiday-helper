import { defineStore } from 'pinia';
import axios from "axios";

export let useApplicationStore = defineStore('applications', {
    state: () => ({
        applications: [],
        managerApplications: [],
        viewing: 'all'
    }),

    actions: {
        async fetchApplications(accountNo) {
            try {
                const resp = await axios.get('/api/applications/' + accountNo);
                this.applications = resp.data;
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        },
        // To do, dynamically added current user account id to replace 0000002L
        async fetchManagerApplications(accountNo){
            try {
                const resp = await axios.get('/api/managerApplications/' + accountNo);
                this.managerApplications = resp.data;
              }
              catch (error) {
                console.log(error)
            }
        },

        addNewApplication(app) {
            this.applications.unshift(app);
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
    },
    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});