import axios from "axios";

axios.defaults.baseURL = "http://127.0.0.1:8000/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

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

    if (status === 401 && !isLoginRequest) {
      clearAuthAndRedirect();
    }

    return Promise.reject(error);
  }
);

export default axios;
