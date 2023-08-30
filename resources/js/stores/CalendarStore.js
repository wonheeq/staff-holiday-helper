import { defineStore } from 'pinia';
import { useUserStore } from './UserStore';
import axios from "axios";

export let useCalendarStore = defineStore('calendar', {
    state: () => ({
        calendarData: [],
    }),

    actions: {
        async fetchCalendarData() {
            try {
                const resp = await axios.get('/api/calendar/' + useUserStore().userId);
                this.calendarData = resp.data;

                // Use unshift to push to front
                this.calendarData.unshift({
                    key: 'today',
                    bar: true,
                    dates: new Date(),
                });
              }
              catch (error) {
                alert(error)
                console.log(error)
            }
        }
    },
});