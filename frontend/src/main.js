import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./router";
import StarRatings from '@hbilal_9/vue3-star-ratings';


const pinia = createPinia();
const app = createApp(App);

app.use(pinia);
app.component('StarRatings', StarRatings);
app.use(router);
app.mount("#app");
