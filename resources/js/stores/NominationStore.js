import { defineStore } from 'pinia';
import axios from 'axios';

export let useNominationStore = defineStore('nominations', {
    state: () => ({
        nominations: [],
        isSelfNominateAll: false,
    }),

    actions: {
        async fetchNominations(accountNo) {
            axios.get('/api/getRolesForNominations/' + accountNo)
            .then(resp => {
                this.nominations.length = 0;
                this.nominations = resp.data;
            })
            .catch (error => {
                console.log(error);
            });
        },
        async fetchNominationsForApplicationNo(applicationNo, accountNo)  {
            axios.get('/api/getNominationsForApplication/' + accountNo + "/" + applicationNo)
            .then(resp => {
                this.nominations.length = 0;
                this.nominations = resp.data;

                this.isSelfNominateAll = true;
                for (let nom of this.nominations) {
                    if (nom.nomination != "Self Nomination") {
                        this.isSelfNominateAll = false;
                        break;
                    }
                }
            })
            .catch(error => {
                console.log(error);
            });
        }
    },

    getters: {

    },
    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
