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

async function ensureValidToken() {
    const token = localStorage.getItem('token');

    if (!token) {
        authChecked = true;
        authValid = false;
        return false;
    }

    if (authChecked && token === lastValidatedToken) {
        return authValid;
    }

    try {
        await axios.get('/auth/validate-token');
        authValid = true;
        lastValidatedToken = token;
    } catch (error) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        authValid = false;
        lastValidatedToken = null;
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
