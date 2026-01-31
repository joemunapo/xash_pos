import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore, useAlertStore } from '../stores';

// Layouts
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import ManagerLayout from "@/layouts/ManagerLayout.vue";

// Auth Pages
import Welcome from "../pages/index.vue";
import Login from "../pages/login.vue";

// POS Pages
import POSDashboard from "../pages/pos/dashboard.vue";
import POSSell from "../pages/pos/sell.vue";
import POSSales from "../pages/pos/sales.vue";
import POSProducts from "../pages/pos/products.vue";
import POSProfile from "../pages/pos/profile.vue";

// Manager Pages
import ManagerDashboard from "../pages/manager/dashboard.vue";
import ManagerSales from "../pages/manager/sales.vue";
import ManagerProducts from "../pages/manager/products.vue";
import ManagerProductDetail from "../pages/manager/ProductDetail.vue";
import ManagerInventory from "../pages/manager/inventory.vue";
import ManagerSalesSummary from "../pages/manager/reports/SalesSummary.vue";
import ManagerCashiers from "../pages/manager/cashiers.vue";
import ManagerProfile from "../pages/manager/profile.vue";

export const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        // Welcome / Landing
        {
            path: '/',
            name: 'welcome',
            component: Welcome,
            meta: { requiresAuth: false }
        },

        // Auth Routes
        {
            path: "/auth",
            component: DefaultLayout,
            children: [
                {
                    path: "login",
                    name: "login",
                    component: Login,
                    meta: { requiresAuth: false }
                },
            ]
        },

        // Redirect /login to /auth/login
        {
            path: '/login',
            redirect: '/auth/login'
        },

        // POS Routes (Protected - Cashiers)
        {
            path: '/pos',
            meta: { requiresAuth: true, role: 'cashier' },
            children: [
                {
                    path: '',
                    redirect: '/pos/dashboard'
                },
                {
                    path: 'dashboard',
                    name: 'pos-dashboard',
                    component: POSDashboard,
                },
                {
                    path: 'sell',
                    name: 'pos-sell',
                    component: POSSell,
                },
                {
                    path: 'sales',
                    name: 'pos-sales',
                    component: POSSales,
                },
                {
                    path: 'products',
                    name: 'pos-products',
                    component: POSProducts,
                },
                {
                    path: 'profile',
                    name: 'pos-profile',
                    component: POSProfile,
                },
            ]
        },

        // Manager Routes (Protected - Managers)
        {
            path: '/manager',
            component: ManagerLayout,
            meta: { requiresAuth: true, role: 'manager' },
            children: [
                {
                    path: '',
                    redirect: '/manager/dashboard'
                },
                {
                    path: 'dashboard',
                    name: 'manager-dashboard',
                    component: ManagerDashboard,
                },
                {
                    path: 'sales',
                    name: 'manager-sales',
                    component: ManagerSales,
                },
                {
                    path: 'products',
                    name: 'manager-products',
                    component: ManagerProducts,
                },
                {
                    path: 'products/:id',
                    name: 'manager-product-detail',
                    component: ManagerProductDetail,
                },
                {
                    path: 'inventory',
                    name: 'manager-inventory',
                    component: ManagerInventory,
                },
                {
                    path: 'reports',
                    redirect: '/manager/reports/sales-summary'
                },
                {
                    path: 'reports/sales-summary',
                    name: 'manager-reports-sales-summary',
                    component: ManagerSalesSummary,
                },
                {
                    path: 'cashiers',
                    name: 'manager-cashiers',
                    component: ManagerCashiers,
                },
                {
                    path: 'profile',
                    name: 'manager-profile',
                    component: ManagerProfile,
                },
            ]
        },

        // Legacy redirect - dashboard goes to role-specific dashboard
        {
            path: '/dashboard',
            redirect: to => {
                const authStore = useAuthStore();
                if (authStore.user?.role === 'manager') {
                    return '/manager/dashboard';
                }
                return '/pos/dashboard';
            }
        },

        // Catch all - redirect to welcome
        {
            path: '/:pathMatch(.*)*',
            redirect: '/'
        }
    ]
});

// Navigation Guard
router.beforeEach(async (to, from, next) => {
    // Clear alerts on route change
    const alertStore = useAlertStore();
    alertStore.clear();

    // Check if route requires authentication
    if (to.meta.requiresAuth) {
        const authStore = useAuthStore();

        if (!authStore.token || !authStore.user) {
            // Save the target route for redirect after login
            authStore.returnUrl = to.fullPath;
            return next('/auth/login');
        }

        // Check role-based access
        if (to.meta.role && authStore.user.role !== to.meta.role) {
            // Redirect to the correct dashboard based on user role
            if (authStore.user.role === 'manager') {
                return next('/manager/dashboard');
            } else if (authStore.user.role === 'cashier') {
                return next('/pos/dashboard');
            }
        }
    }

    // If user is logged in and trying to access login page, redirect to role-specific dashboard
    if (to.name === 'login' || to.name === 'welcome') {
        const authStore = useAuthStore();
        if (authStore.token && authStore.user) {
            if (authStore.user.role === 'manager') {
                return next('/manager/dashboard');
            } else {
                return next('/pos/dashboard');
            }
        }
    }

    next();
});

export default router;
