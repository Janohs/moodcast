import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import authService from '../services/authService.js'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/auth',
      name: 'auth',
      component: () => import('../components/AuthPage.vue')
    },
    {
      path: '/preferences',
      name: 'preferences',
      component: () => import('../components/PreferencesSetup.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../components/Dashboard.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/mood-entry',
      name: 'mood-entry',
      component: () => import('../components/MoodEntry.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/insights',
      name: 'insights',
      component: () => import('../components/Insights.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/input-test',
      name: 'input-test',
      component: () => import('../components/InputTest.vue')
    },
    {
      path: '/database-test',
      name: 'database-test',
      component: () => import('../components/DatabaseTest.vue')
    },
    {
      path: '/weather-test',
      name: 'weather-test',
      component: () => import('../components/WeatherServiceTest.vue')
    },
    {
      path: '/input-test',
      name: 'input-test',
      component: () => import('../components/InputTest.vue')
    }
  ]
})

// Navigation guard for protected routes
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !authService.isAuthenticated()) {
    next('/auth')
  } else if (to.path === '/auth' && authService.isAuthenticated()) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
