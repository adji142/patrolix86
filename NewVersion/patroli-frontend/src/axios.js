import axios from 'axios';

axios.defaults.withCredentials = true;
// axios.defaults.baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
axios.defaults.baseURL = 'http://192.168.1.9:8000/api';

axios.interceptors.response.use(response => {
    return response;
}, error => {
    if (error.response) {
        if (error.response.status === 401) {
            if (!window.location.pathname.includes('/login')) {
                localStorage.removeItem('token');
                window.location.href = '/login';
            }
        }
        
        // Handle subscription expiry
        if (error.response.status === 403 && error.response.data.subscription_expired) {
            // We can't easily access Pinia here without circular deps sometimes, 
            // but we can trigger a page refresh to let the router guard take over
            // or just broadcast an event.
            if (!window.location.pathname.includes('/my-subscription')) {
                window.location.href = '/my-subscription';
            }
        }
    }
    return Promise.reject(error);
});

// Add token and branch ID if exists
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    const branch = JSON.parse(localStorage.getItem('activeBranch'));

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    if (branch && branch.id) {
        config.headers['X-Branch-ID'] = branch.id;
    }

    return config;
});

export default axios;