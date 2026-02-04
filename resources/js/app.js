import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import "./axios";

// 🔹 CSS
import "bootstrap/dist/css/bootstrap.min.css";
import "admin-lte/dist/css/adminlte.min.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "bootstrap-icons/font/bootstrap-icons.css";

import "bootstrap/dist/js/bootstrap.bundle.min.js"; // inclut Popper

// AdminLTE
import "admin-lte/dist/js/adminlte.min.js";


createApp(App).use(router).mount("#app");
