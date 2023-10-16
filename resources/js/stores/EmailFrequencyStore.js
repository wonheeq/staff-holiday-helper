import { defineStore } from 'pinia';
import axios from "axios";

export let useEmailFrequencyStore = defineStore('emailFrequency', {
    state: () => ({
        frequency: null,
    }),

    actions: {
        async getFrequency() {
            axios.get('/api/getEmailFrequency')
            .then(resp => {
                this.frequency = resp.data;
            })
            .catch(error => {
                console.log(error);
            });
        },
        async setFrequency(accountNo, freq) {
            let result = false;
            await axios.post("/api/setEmailFrequency", {
                accountNo: accountNo,
                frequency: freq
            })
            .then(resp => {
                this.frequency = freq;
                result = true;
            })
            .catch(_ => {
                result = false;
            });
            return result;
        }
    },

    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
