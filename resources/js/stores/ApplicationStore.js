import { defineStore } from 'pinia';
import { useUserStore } from './UserStore';
import axios from "axios";

export let useApplicationStore = defineStore('applications', {
    state: () => ({
        applications: [],
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