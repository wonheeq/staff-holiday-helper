import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { setupCalendar } from 'v-calendar';
import { createPinia } from 'pinia';
import dayjs from 'dayjs';
import vTitle from 'vuejs-title'
import piniaPluginPersistedState from "pinia-plugin-persistedstate";
import { useDark } from "@vueuse/core";
const appName = "LeaveOnTime";

createInertiaApp({
    title: () => `${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(setupCalendar, {})
            .use(createPinia().use(piniaPluginPersistedState))
            .use(vTitle, {
                transitionDelay: 0,
                transitionDuration: 100,
            })
            .use(useDark)
            .provide('dayJS', dayjs)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});