import { defineStore } from 'pinia';

export let useUserStore = defineStore('user', {
    state: () => ({
        userId: "000000s",
    }),

    getters: {
        getUserId() {
            return this.userId;
        }
    }
});