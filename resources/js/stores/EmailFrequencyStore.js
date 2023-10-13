import { defineStore } from 'pinia';
import axios from "axios";

export let useEmailFrequencyStore = defineStore('emailFrequency', {
    state: () => ({
        frequency: null,
    }),

    actions: {
        async getFrequency() {
            try {
                const resp = await axios.get('/api/getEmailFrequency');
                this.frequency = resp.data;
              }
              catch (error) {
                console.log(error)
            }
        },
        async setFrequency(accountNo, freq) {
            try {
                const resp = await axios.post("/api/setEmailFrequency", {
                    accountNo: accountNo,
                    frequency: freq
                });

                if (resp.status == 200) {
                    this.frequency = freq;
                    return true;
                }
            }
            catch (error) {}
            return false;
        }
    },

    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
