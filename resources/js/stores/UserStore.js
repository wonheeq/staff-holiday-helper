import { defineStore } from 'pinia';

export let useUserStore = defineStore('user', {
    state: () => ({
        userId: null,
    }),

    getters: {
        getUserId() {
            return this.userId;
        }
    },

    actions: {
        setUserId(id) {
            this.userId = id;
        } 
    }
});