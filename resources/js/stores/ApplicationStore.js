import { defineStore } from 'pinia';
import axios from "axios";

export let useApplicationStore = defineStore('applications', {
    state: () => ({
        applications: [],
    }),

    actions: {
        async fetchApplications() {
            try {
                const resp = await axios.get('/api/applications');
                this.applications = resp.data;
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        }
    },
/*
    getters: {
        filteredMessages(viewing) {
            return this.messages.filter(message => message.acknowledged === 0);
        }
    }
*/
});