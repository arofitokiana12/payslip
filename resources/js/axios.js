import axios from "axios";

axios.defaults.baseURL = "http://127.0.0.1:8000/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

function debugLog(runId, hypothesisId, location, message, data = {}) {
  // #region agent log
  fetch('http://127.0.0.1:7492/ingest/76f99d8d-fafa-4ba4-8b86-b404e25516e7',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'4769f4'},body:JSON.stringify({sessionId:'4769f4',runId,hypothesisId,location,message,data,timestamp:Date.now()})}).catch(()=>{});
  // #endregion
}

const clearAuthAndRedirect = () => {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
  const isOnLoginPage = window.location.pathname === "/";
  if (!isOnLoginPage) {
    window.location.href = "/";
  }
};

axios.interceptors.request.use(config => {
  const token = localStorage.getItem("token");
  debugLog('run1', 'H2', 'axios.js:request', 'Outgoing request', {
    url: config?.url || null,
    method: config?.method || null,
    hasToken: !!token,
    hasAcceptHeader: !!(config?.headers?.Accept || axios.defaults.headers.common["Accept"])
  });

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  } else if (config.headers?.Authorization) {
    delete config.headers.Authorization;
  }

  return config;
});

axios.interceptors.response.use(
  response => response,
  error => {
    const status = error?.response?.status;
    const requestUrl = error?.config?.url || "";
    const isLoginRequest = requestUrl.includes("/login");
    debugLog('run1', 'H3', 'axios.js:responseError', 'Axios response error', {
      status: status || null,
      requestUrl,
      isLoginRequest
    });

    if (status === 401 && !isLoginRequest) {
      clearAuthAndRedirect();
    }

    return Promise.reject(error);
  }
);

export default axios;
