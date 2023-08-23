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
                alert(error)
                console.log(error)
            }
        }
    },

    getters: {
        filteredMessages() {
            if (this.viewing === 'unread') {
                return this.messages.filter(message => message.acknowledged === 0);
            }
            else {
                return this.messages;
            }
        },
        unreadMessages() {
            return this.messages.filter(message => message.acknowledged === 0);
        }
    }
});