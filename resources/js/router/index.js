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


        ],
    },
]


const router = createRouter({
    history: createWebHistory(),
    routes,
})
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')  // Vérifie si le token existe

    if (to.meta.requiresAuth && !token) {
        // Si la route nécessite auth mais pas de token → redirige vers login
        next({ name: 'Login' })
    } else if (to.name === 'Login' && token) {
        // Si l'utilisateur est déjà connecté et veut aller sur login → dashboard
        next({ name: 'Dashboard' })
    } else {
        // Sinon, continue normalement
        next()
    }
})

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem("token");

    if (to.meta.requiresAuth && !token) {
        next("/login");
    } else {
        next();
    }
});


export default router
