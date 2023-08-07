import { defineStore } from 'pinia';
import { useUserStore } from './UserStore';

export let useNominationStore = defineStore('nominations', {
    state: () => ({
        nominations: [],
    }),

    actions: {
        async fetchNominations() {
            try {
                const resp = await axios.get('/api/getRolesForNominations/' + useUserStore().userId);
                this.nominations = resp.data;
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        },
        async fetchNominationsForApplicationNo(applicationNo)  {
            try {
                const resp = await axios.get('/api/getNominationsForApplication/' + useUserStore().userId + "/" + applicationNo);
                this.nominations = resp.data;
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        }
    },

    getters: {
        
    }
});