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
            axios.post("/api/setEmailFrequency", {
                accountNo: accountNo,
                frequency: freq
            })
            .then(_ => {
                this.frequency = freq;
                return true;
            })
            .catch(_ => {
                return false;
            });
        }
    },

    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
