import { createRouter, createWebHistory } from 'vue-router'
import Login from '../pages/Login.vue'
import AdminLayout from "../layouts/AdminLayout.vue"
import Dashboard from "../pages/Dashboard.vue"
import Employees from "../pages/Employees/index.vue"
import Positions from "../pages/Positions/index.vue"
import Payroll from "../pages/Payroll/index.vue"
import Settings from "../pages/Settings/index.vue"
import Users from "../pages/Users/index.vue"
import Roles from "../pages/Roles/index.vue"
import Companies from "../pages/Companies/index.vue"
import Attendance from '../pages/Attendance/index.vue';
import Leaves from '../pages/Leaves/index.vue';
import axios from '../axios';



const routes = [
    { path: '/', name: 'Login', component: Login },

    {
        path: "/admin",
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: "dashboard",
                name: 'Dashboard',
                component: Dashboard,
            },

            {
                path: "employees",
                component: Employees,
            },

            {

                path: 'positions',
                name: 'positions',
                component: Positions

            },

            {
                path: 'users',
                name: 'users',
                component: Users
            },

            {
                path: 'roles',
                name: 'roles',
                component: Roles
            },

            {
                path: 'companies',
                name: 'companies',
                component: Companies
            },

            {
                path: "attendance",
                component: Attendance,
            },

            {
                path: "leaves",
                component: Leaves,
            },

            {
                path: "payroll",
                component: Payroll,
            },

            {
                path: "settings",
                component: Settings,
            },

            {
                path: 'attendance',
                name: 'attendance',
                component: Attendance
            },

            {
                path: 'leaves',
                name: 'leaves',
                component: Leaves
            },

            { path: 'payroll',
             name: 'payroll',
              component: Payroll
            },


        ],
    },
]


const router = createRouter({
    history: createWebHistory(),
    routes,
})
let authChecked = false;
let authValid = false;
let lastValidatedToken = null;

function debugLog(runId, hypothesisId, location, message, data = {}) {
    // #region agent log
    fetch('http://127.0.0.1:7492/ingest/76f99d8d-fafa-4ba4-8b86-b404e25516e7',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'4769f4'},body:JSON.stringify({sessionId:'4769f4',runId,hypothesisId,location,message,data,timestamp:Date.now()})}).catch(()=>{});
    // #endregion
}

async function ensureValidToken() {
    const token = localStorage.getItem('token');
    debugLog('run1', 'H1', 'router/index.js:ensureValidToken:entry', 'ensureValidToken called', {
        hasToken: !!token,
        authChecked,
        authValid,
        hasLastValidatedToken: !!lastValidatedToken,
        sameAsLastValidatedToken: !!token && token === lastValidatedToken
    });

    if (!token) {
        authChecked = true;
        authValid = false;
        debugLog('run1', 'H1', 'router/index.js:ensureValidToken:noToken', 'No token found in localStorage', {});
        return false;
    }

    if (authChecked && token === lastValidatedToken) {
        debugLog('run1', 'H4', 'router/index.js:ensureValidToken:cacheHit', 'Using cached auth status', {
            authValid
        });
        return authValid;
    }

    try {
        await axios.get('/auth/validate-token');
        authValid = true;
        lastValidatedToken = token;
        debugLog('run1', 'H3', 'router/index.js:ensureValidToken:validateSuccess', 'validate-token success', {});
    } catch (error) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        authValid = false;
        lastValidatedToken = null;
        debugLog('run1', 'H3', 'router/index.js:ensureValidToken:validateError', 'validate-token error in router', {
            status: error?.response?.status || null,
            code: error?.code || null,
            url: error?.config?.url || null
        });
    } finally {
        authChecked = true;
    }

    return authValid;
}

router.beforeEach(async (to) => {
    const isAuthenticated = await ensureValidToken();

    if (to.meta.requiresAuth && !isAuthenticated) {
        return { name: 'Login' };
    }

    if (to.name === 'Login' && isAuthenticated) {
        return { name: 'Dashboard' };
    }

    return true;
});


export default router
