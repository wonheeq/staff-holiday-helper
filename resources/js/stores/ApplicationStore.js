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
            axios.get('/api/applications/' + accountNo)
            .then(resp => {
                this.applications.length = 0;
                this.applications = resp.data;
            })
            .catch(error => {
                console.log(error);
            });
        },
        async fetchManagerApplications(accountNo){
            axios.get('/api/managerApplications/' + accountNo)
            .then((resp) => {
                this.managerApplications.length = 0;
                this.managerApplications = resp.data;
            })
            .catch ((error) =>  {
                console.log(error)
            });
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
    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
