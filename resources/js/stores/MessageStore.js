import { defineStore } from 'pinia';
import axios from "axios";

export let useMessageStore = defineStore('messages', {
    state: () => ({
        messages: [],
        viewing: 'unread'
    }),

    actions: {
        async fetchMessages(accountNo) {
            axios.get('/api/messages/' + accountNo)
            .then(resp => {
                this.messages.length = 0;
                this.messages = resp.data;
            })
            .catch(error => {
                console.log(error);
            })
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
