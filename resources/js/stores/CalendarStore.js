import { defineStore } from 'pinia';
import axios from "axios";

export let useCalendarStore = defineStore('calendar', {
    state: () => ({
        calendarData: [],
    }),

    actions: {
        async fetchCalendarData(accountNo) {
            try {
                const resp = await axios.get('/api/calendar/' + accountNo);
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