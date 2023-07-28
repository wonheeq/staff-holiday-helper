import { defineStore } from 'pinia';
import { useUserStore } from './UserStore';
import axios from "axios";

export let useMessageStore = defineStore('messages', {
    state: () => ({
        messages: [],
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
        filteredMessages(viewing) {
            return this.messages.filter(message => message.acknowledged === 0);
        }
    }
});