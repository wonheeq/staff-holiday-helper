import { defineStore } from 'pinia';
import axios from "axios";
import Swal from 'sweetalert2';

export let useReminderTimeframeStore = defineStore('reminderTimeframe', {
    state: () => ({
        reminderTimeframe: null,
    }),

    actions: {
        async getReminderTimeframe(accountNo) {
            axios.get('/api/getReminderTimeframe/' + accountNo)
            .then(resp => {
                this.reminderTimeframe = resp.data;
            })
            .catch(error => {
                console.log(error);
            });
        },
        async setReminderTimeframe(accountNo, rtf) {
            axios.post("/api/setReminderTimeframe", {
                accountNo: accountNo,
                timeframe: rtf
            })
            .then(_ => {
                this.reminderTimeframe = rtf;
                Swal.fire({
                    icon: "success",
                    title: 'Successfully changed reminder timeframe',
                });
            })
            .catch(error => {
                console.log(error);
                Swal.fire({
                    icon: "error",
                    title: 'Failed to change reminder timeframe, please try again',
                });
            });
        }
    },

    persist: {
        storage: sessionStorage, // data in sessionStorage is cleared when the page session ends.
    },
});
