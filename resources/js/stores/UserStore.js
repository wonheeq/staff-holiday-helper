import { defineStore } from 'pinia';

export let useUserStore = defineStore('user', {
    state: () => ({
        userId: localStorage.getItem('accountNo'),
    }),

    getters: {
        getUserId() {
            if (this.userId == null) {
                return localStorage.getItem('accountNo');
            }
            return this.userId;
        }
    },

    actions: {
        setUserId(id) {
            this.userId = id;
            localStorage.setItem('accountNo', id);
        } 
    }
});