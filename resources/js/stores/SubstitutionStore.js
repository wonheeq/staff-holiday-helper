import { defineStore } from 'pinia';
import axios from "axios";

export let useSubstitutionStore = defineStore('substitutions', {
    state: () => ({
        substitutions: [],
    }),

    actions: {
        async fetchSubstitutions(accountNo) {
            try {
                const resp = await axios.get('/api/getSubstitutionsForUser/' + accountNo);
                this.substitutions = resp.data;
              }
              catch (error) {
                console.log(error)
            }
        }
    },
    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
