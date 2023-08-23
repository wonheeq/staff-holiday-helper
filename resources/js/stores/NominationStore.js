import { defineStore } from 'pinia';

export let useNominationStore = defineStore('nominations', {
    state: () => ({
        nominations: [],
        isSelfNominateAll: false,
    }),

    actions: {
        async fetchNominations(accountNo) {
            try {
                const resp = await axios.get('/api/getRolesForNominations/' + accountNo);
                this.nominations = resp.data;
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        },
        async fetchNominationsForApplicationNo(applicationNo, accountNo)  {
            try {
                const resp = await axios.get('/api/getNominationsForApplication/' + accountNo + "/" + applicationNo);
                this.nominations = resp.data;
                
                this.isSelfNominateAll = true;
                for (let nom of this.nominations) {
                    if (nom.nomination != "Self Nomination") {
                        this.isSelfNominateAll = false;
                        break;
                    }
                }

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