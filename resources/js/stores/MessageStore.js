import { defineStore } from 'pinia';
import { useUserStore } from './UserStore';
import axios from "axios";

export let useMessageStore = defineStore('messages', {
    state: () => ({
        messages: [],
        viewing: 'unread'
    }),

    actions: {
        async fetchMessages() {
            try {
                const resp = await axios.get('/api/messages/' + useUserStore().userId);
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