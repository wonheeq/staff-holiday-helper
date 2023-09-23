import { defineStore } from 'pinia';

export let useScreenSizeStore = defineStore('screenSize', {
    state: () => ({
        isMobile: false,
    }),

    actions: {
        updateWidth(w) {
            let isMobileSize = w < 768;
            if (this.isMobile != isMobileSize) {
                this.isMobile = isMobileSize;
            }
        }
    },
});