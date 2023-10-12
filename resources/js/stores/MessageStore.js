import { defineStore } from 'pinia';
import axios from "axios";

export let useMessageStore = defineStore('messages', {
    state: () => ({
        messages: [],
        viewing: 'unread'
    }),

    actions: {
        async fetchMessages(accountNo) {
            try {
                const resp = await axios.get('/api/messages/' + accountNo);
                this.messages = resp.data;
              }
              catch (error) {
                console.log(error)
            }
        }
    },

    getters: {
        unreadMessages() {
            return this.messages.filter(message => message.acknowledged === 0);
        }
    },
    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
