import '../css/app.css';
import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import AppLayout from './Layouts/AppLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Antech Async HRIS';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => {
    const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true });
    const page = pages[`./Pages/${name}.vue`];
    if (!page) {
      throw new Error(`Page not found: ${name}`);
    }
    // Persistent layout: all pages use AppLayout unless they define their own
    page.default.layout = page.default.layout || AppLayout;
    return page;
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .component('Link', Link)
      .component('Head', Head)
      .mount(el);
  },
  progress: {
    color: '#E67E22',
  },
});
