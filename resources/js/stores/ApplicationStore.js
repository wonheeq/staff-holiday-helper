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

        addNewApplication(app, oldAppNo) {
            if (oldAppNo != null) {
                let index = 0;
                for (const app of this.applications) {
                    if (app.applicationNo == oldAppNo) {
                        this.applications.splice(index, 1);
                        break;
                    }
                    index++;
                }
            }
            this.applications.unshift(app);
        }
    },
    getters: {
        allApplications(){
            return this.managerApplications.filter(application => (application.status ==='N' || application.status ==='Y' || application.status ==='U') && application.status !=='C' && application.status !== 'E');
        },
        acceptedApplications(){
            return this.managerApplications.filter(application => application.status === 'Y');
        },
        rejectedApplications(){
            return this.managerApplications.filter(application => application.status === 'N');
        },
        unacknowledgeApplications(){
            return this.managerApplications.filter(application => application.status === 'U');
        },
    },
    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});