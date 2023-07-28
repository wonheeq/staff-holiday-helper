import { defineStore } from 'pinia';

export let useUserStore = defineStore('user', {
    state: () => ({
        userId: "a000000",
    }),

    getters: {
        getUserId() {
            return this.userId;
        }
    }
});