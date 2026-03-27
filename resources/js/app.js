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

function debugLog(runId, hypothesisId, location, message, data = {}) {
  // #region agent log
  fetch('http://127.0.0.1:7492/ingest/76f99d8d-fafa-4ba4-8b86-b404e25516e7',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'4769f4'},body:JSON.stringify({sessionId:'4769f4',runId,hypothesisId,location,message,data,timestamp:Date.now()})}).catch(()=>{});
  // #endregion
}

async function validateStoredToken() {
  const token = localStorage.getItem("token");
  debugLog('run1', 'H4', 'app.js:validateStoredToken:start', 'App startup token validation', {
    hasToken: !!token
  });

  if (!token) return;

  try {
    await axios.get("/auth/validate-token");
    debugLog('run1', 'H4', 'app.js:validateStoredToken:success', 'Startup validate-token success', {});
  } catch (error) {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    debugLog('run1', 'H4', 'app.js:validateStoredToken:error', 'Startup validate-token error', {
      status: error?.response?.status || null,
      url: error?.config?.url || null
    });
  }
}

validateStoredToken().finally(() => {
  createApp(App).use(router).use(i18n).mount("#app");
});
