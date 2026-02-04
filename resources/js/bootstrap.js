import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// IMPORTANT : baseURL doit pointer vers /api
window.axios.defaults.baseURL = 'http://localhost:8000/api';

// CSRF Token pour Laravel
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


axios.interceptors.request.use(config => {
   // Dans bootstrap.js, ajoutez ça :
const token = localStorage.getItem("token");
if (token) {
  window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

    return config;
});

export default axios;
