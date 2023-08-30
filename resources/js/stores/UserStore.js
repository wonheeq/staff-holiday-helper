import { defineStore } from 'pinia';

export let useUserStore = defineStore('user', {
    state: () => ({
        userId: "000000a",
    }),

    getters: {
        getUserId() {
            return this.userId;
        }
    }
});