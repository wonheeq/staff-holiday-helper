import { defineStore } from 'pinia';

export let useNominationStore = defineStore('nominations', {
    state: () => ({
        nominations: [
            {
                selected: false,
                role: 'COMP2007: Something something',
                nomination: "",
                visible: true,
            },
            {
                selected: false,
                role: 'COMP3007: Something something',
                nomination: "",
                visible: true,
            },
            {
                selected: false,
                role: 'COMP3001: Something something',
                nomination: "",
                visible: true,
            },
            {
                selected: false,
                role: 'ISEC2001: Something something',
                nomination: "",
                visible: true,
            },
            {
                selected: false,
                role: 'ISEC2007: Something something',
                nomination: "",
                visible: true,
            },
            {
                selected: false,
                role: 'ISAD3001: Something something',
                nomination: "",
                visible: true,
            },
        ],
    }),

    actions: {
        
    },

    getters: {
        
    }
});