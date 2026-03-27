import './bootstrap';
import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import i18n from './plugins/i18n';
import axios from "./axios";

import $ from 'jquery';
window.$ = window.jQuery = $;

// CSS: Bootstrap + AdminLTE d'abord, puis thème premium en dernier pour priorité
import "bootstrap/dist/css/bootstrap.min.css";
import "admin-lte/dist/css/adminlte.min.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "bootstrap-icons/font/bootstrap-icons.css";
import "../css/premium-theme.css";

import "bootstrap/dist/js/bootstrap.bundle.min.js"; // inclut Popper

// AdminLTE
import "admin-lte/dist/js/adminlte.min.js";

async function validateStoredToken() {
  const token = localStorage.getItem("token");

  if (!token) return;

  try {
    await axios.get("/auth/validate-token");
    return true;
  } catch (error) {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    return false;
  }

  return true;
}

async function validateAndEnforceCurrentToken(trigger) {
  const token = localStorage.getItem("token");

  if (!token) {
    return;
  }

  try {
    await axios.get("/auth/validate-token");
  } catch (error) {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    if (window.location.pathname !== "/") {
      window.location.href = "/";
    }
  }
}

function startTokenTamperMonitor() {
  let lastToken = localStorage.getItem("token");

  window.setInterval(() => {
    const currentToken = localStorage.getItem("token");
    if (currentToken !== lastToken) {
      lastToken = currentToken;
      validateAndEnforceCurrentToken("interval-token-changed");
    }
  }, 1000);

  window.addEventListener("focus", () => validateAndEnforceCurrentToken("window-focus"));
  document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") {
      validateAndEnforceCurrentToken("visibility-visible");
    }
  });
}

validateStoredToken().finally(() => {
  startTokenTamperMonitor();
  createApp(App).use(router).use(i18n).mount("#app");
});
