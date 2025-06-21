<template>
  <div class="min-h-screen flex items-center justify-center px-4 bg-gradient-to-br from-blue-500 via-purple-600 to-indigo-700">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    
    <!-- Floating particles background -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="floating-particles">
        <div v-for="i in 6" :key="i" class="particle" :style="{ animationDelay: `${i * 0.5}s` }"></div>
      </div>
    </div>

    <div class="relative max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <div class="mb-4">
          <h1 class="text-5xl font-bold text-white mb-2 tracking-tight">
            <span class="inline-block animate-pulse">🌦️</span> MoodCast
          </h1>
          <div class="h-1 w-20 bg-gradient-to-r from-blue-400 to-purple-400 mx-auto rounded-full"></div>
        </div>
        <p class="text-white/90 text-lg font-medium">Track your mood with the weather</p>
        <p class="text-white/70 text-sm mt-2">Discover how weather affects your emotions</p>
      </div>

      <!-- Auth Form -->
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 shadow-2xl border border-white/20 transition-all duration-300 hover:bg-white/15">
        <!-- Tab Toggle -->
        <div class="flex bg-white/20 rounded-xl p-1 mb-6 relative overflow-hidden">
          <div 
            class="absolute top-1 bottom-1 bg-white rounded-lg transition-all duration-300 ease-out shadow-lg"
            :style="{ 
              left: isLogin ? '4px' : '50%', 
              width: 'calc(50% - 4px)',
              transform: isLogin ? 'translateX(0)' : 'translateX(-50%)'
            }"
          ></div>
          <button
            @click="switchToLogin"
            :class="[
              'flex-1 py-2.5 px-4 rounded-lg text-sm font-medium transition-all duration-300 relative z-10',
              isLogin ? 'text-gray-900' : 'text-white hover:text-white/90'
            ]"
          >
            <span class="flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
              </svg>
              Sign In
            </span>
          </button>
          <button
            @click="switchToSignup"
            :class="[
              'flex-1 py-2.5 px-4 rounded-lg text-sm font-medium transition-all duration-300 relative z-10',
              !isLogin ? 'text-gray-900' : 'text-white hover:text-white/90'
            ]"
          >
            <span class="flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
              Sign Up
            </span>
          </button>
        </div>

        <!-- Login Form -->
        <Transition 
          name="slide-fade" 
          mode="out-in"
        >
          <form v-if="isLogin" @submit.prevent="handleLogin" class="space-y-6" key="login">
            <div class="space-y-4">
              <div class="form-group">
                <label class="block text-white text-sm font-medium mb-2 flex items-center gap-2">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  Email Address
                </label>
                <input
                  v-model="loginForm.email"
                  type="email"
                  required
                  :class="[
                    'w-full px-4 py-3 rounded-lg bg-white/15 border border-white/30 text-white placeholder-white/60 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 focus:bg-white/20',
                    errors.email ? 'border-red-400/70 bg-red-500/10' : 'border-white/30 hover:border-white/40'
                  ]"
                  placeholder="Enter your email address"
                />
                <Transition name="error-fade">
                  <div v-if="errors.email" class="mt-1.5 flex items-center gap-1.5 text-red-300 text-xs">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ errors.email }}
                  </div>
                </Transition>
              </div>
              
              <div class="form-group">
                <label class="block text-white text-sm font-medium mb-2 flex items-center gap-2">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                  </svg>
                  Password
                </label>
                <input
                  v-model="loginForm.password"
                  type="password"
                  required
                  :class="[
                    'w-full px-4 py-3 rounded-lg bg-white/15 border border-white/30 text-white placeholder-white/60 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 focus:bg-white/20',
                    errors.password ? 'border-red-400/70 bg-red-500/10' : 'border-white/30 hover:border-white/40'
                  ]"
                  placeholder="Enter your password"
                />
                <Transition name="error-fade">
                  <div v-if="errors.password" class="mt-1.5 flex items-center gap-1.5 text-red-300 text-xs">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ errors.password }}
                  </div>
                </Transition>
              </div>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.01] hover:shadow-lg flex items-center justify-center gap-2"
            >
              <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
              </svg>
              {{ loading ? 'Signing you in...' : 'Sign In' }}
            </button>
          </form>

          <!-- Register Form -->
          <form v-else @submit.prevent="handleRegister" class="space-y-5" key="register">
            <div class="space-y-4">
              <div class="form-group">
                <label class="block text-white text-sm font-medium mb-2 flex items-center gap-2">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  Full Name
                </label>
                <input
                  v-model="registerForm.name"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-3 rounded-lg bg-white/15 border border-white/30 text-white placeholder-white/60 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400/50 focus:bg-white/20',
                    errors.name ? 'border-red-400/70 bg-red-500/10' : 'border-white/30 hover:border-white/40'
                  ]"
                  placeholder="Enter your full name"
                />
                <Transition name="error-fade">
                  <div v-if="errors.name" class="mt-1.5 flex items-center gap-1.5 text-red-300 text-xs">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ errors.name }}
                  </div>
                </Transition>
              </div>

              <div class="form-group">
                <label class="block text-white text-sm font-medium mb-2 flex items-center gap-2">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  Email Address
                </label>
                <input
                  v-model="registerForm.email"
                  type="email"
                  required
                  :class="[
                    'w-full px-4 py-3 rounded-lg bg-white/15 border border-white/30 text-white placeholder-white/60 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400/50 focus:bg-white/20',
                    errors.email ? 'border-red-400/70 bg-red-500/10' : 'border-white/30 hover:border-white/40'
                  ]"
                  placeholder="Enter your email address"
                />
                <Transition name="error-fade">
                  <div v-if="errors.email" class="mt-1.5 flex items-center gap-1.5 text-red-300 text-xs">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ errors.email }}
                  </div>
                </Transition>
              </div>
              
              <div class="form-group">
                <label class="block text-white text-sm font-medium mb-2 flex items-center gap-2">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                  </svg>
                  Password
                </label>
                <input
                  v-model="registerForm.password"
                  type="password"
                  required
                  :class="[
                    'w-full px-4 py-3 rounded-lg bg-white/15 border border-white/30 text-white placeholder-white/60 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400/50 focus:bg-white/20',
                    errors.password ? 'border-red-400/70 bg-red-500/10' : 'border-white/30 hover:border-white/40'
                  ]"
                  placeholder="Create a secure password (min 6 characters)"
                />
                <Transition name="error-fade">
                  <div v-if="errors.password" class="mt-1.5 flex items-center gap-1.5 text-red-300 text-xs">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ errors.password }}
                  </div>
                </Transition>
              </div>

              <div class="form-group">
                <label class="block text-white text-sm font-medium mb-2 flex items-center gap-2">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  Confirm Password
                </label>
                <input
                  v-model="registerForm.confirmPassword"
                  type="password"
                  required
                  :class="[
                    'w-full px-4 py-3 rounded-lg bg-white/15 border border-white/30 text-white placeholder-white/60 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-400/50 focus:border-green-400/50 focus:bg-white/20',
                    errors.confirmPassword ? 'border-red-400/70 bg-red-500/10' : 'border-white/30 hover:border-white/40'
                  ]"
                  placeholder="Confirm your password"
                />
                <Transition name="error-fade">
                  <div v-if="errors.confirmPassword" class="mt-1.5 flex items-center gap-1.5 text-red-300 text-xs">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ errors.confirmPassword }}
                  </div>
                </Transition>
              </div>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.01] hover:shadow-lg flex items-center justify-center gap-2"
            >
              <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
              {{ loading ? 'Creating your account...' : 'Create Account' }}
            </button>
          </form>
        </Transition>

        <!-- Alert Messages -->
        <Transition name="alert-fade">
          <div v-if="error" class="mt-4 p-3 bg-red-500/15 border border-red-500/40 rounded-lg backdrop-blur-sm">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-red-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
              <p class="text-red-200 text-sm font-medium">{{ error }}</p>
            </div>
          </div>
        </Transition>

        <Transition name="alert-fade">
          <div v-if="successMessage" class="mt-4 p-3 bg-green-500/15 border border-green-500/40 rounded-lg backdrop-blur-sm">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-green-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <p class="text-green-200 text-sm font-medium">{{ successMessage }}</p>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Test Links (only in development) -->
      <div v-if="isDevelopment" class="text-center space-y-3 mt-8">
        <p class="text-white/60 text-sm font-medium">Development Tools</p>
        <div class="flex justify-center space-x-6">
          <router-link 
            to="/database-test" 
            class="text-blue-300 hover:text-blue-200 text-sm underline underline-offset-2 transition-colors duration-200 flex items-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
            </svg>
            Database Test
          </router-link>
          <router-link 
            to="/weather-test" 
            class="text-blue-300 hover:text-blue-200 text-sm underline underline-offset-2 transition-colors duration-200 flex items-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
            </svg>
            Weather Test
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/authService.js'

const router = useRouter()

// Reactive state
const isLogin = ref(true)
const loading = ref(false)
const error = ref('')
const successMessage = ref('')
const errors = reactive({})

// Development mode check
const isDevelopment = computed(() => import.meta.env.DEV)

// Form data
const loginForm = reactive({
  email: '',
  password: ''
})

const registerForm = reactive({
  name: '',
  email: '',
  password: '',
  confirmPassword: ''
})

// Clear messages
function clearMessages() {
  error.value = ''
  successMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])
}

// Clear forms
function clearForms() {
  Object.assign(loginForm, { email: '', password: '' })
  Object.assign(registerForm, { name: '', email: '', password: '', confirmPassword: '' })
}

// Switch between modes
function switchToLogin() {
  if (!isLogin.value) {
    isLogin.value = true
    clearMessages()
    clearForms()
  }
}

function switchToSignup() {
  if (isLogin.value) {
    isLogin.value = false
    clearMessages()
    clearForms()
  }
}

// Enhanced client-side validation
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function validateRegistrationForm() {
  clearMessages()
  
  let isValid = true
  
  // Name validation
  if (!registerForm.name.trim()) {
    errors.name = 'Full name is required'
    isValid = false
  } else if (registerForm.name.trim().length < 2) {
    errors.name = 'Name must be at least 2 characters long'
    isValid = false
  }
  
  // Email validation
  if (!registerForm.email.trim()) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!validateEmail(registerForm.email)) {
    errors.email = 'Please enter a valid email address'
    isValid = false
  }
  
  // Password validation
  if (!registerForm.password) {
    errors.password = 'Password is required'
    isValid = false
  } else if (registerForm.password.length < 6) {
    errors.password = 'Password must be at least 6 characters long'
    isValid = false
  } else if (!/(?=.*[a-z])(?=.*[A-Z])|(?=.*\d)/.test(registerForm.password)) {
    errors.password = 'Password should contain uppercase, lowercase, or numbers'
    isValid = false
  }
  
  // Confirm password validation
  if (!registerForm.confirmPassword) {
    errors.confirmPassword = 'Please confirm your password'
    isValid = false
  } else if (registerForm.password !== registerForm.confirmPassword) {
    errors.confirmPassword = 'Passwords do not match'
    isValid = false
  }
  
  return isValid
}

function validateLoginForm() {
  clearMessages()
  
  let isValid = true
  
  // Email validation
  if (!loginForm.email.trim()) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!validateEmail(loginForm.email)) {
    errors.email = 'Please enter a valid email address'
    isValid = false
  }
  
  // Password validation
  if (!loginForm.password) {
    errors.password = 'Password is required'
    isValid = false
  }
  
  return isValid
}

// Handle login
async function handleLogin() {
  if (loading.value) return
  
  if (!validateLoginForm()) return
  
  loading.value = true

  try {
    const result = await authService.login(loginForm.email, loginForm.password)
    
    if (result.success) {
      successMessage.value = '🎉 Welcome back! Redirecting...'
      
      // Check if user has weather preferences
      const user = authService.getCurrentUser()
      const preferences = user?.weather_preferences || {}
      
      setTimeout(() => {
        if (Object.keys(preferences).length === 0) {
          // Redirect to preferences setup
          router.push('/preferences')
        } else {
          // Redirect to dashboard
          router.push('/dashboard')
        }
      }, 1500)
    } else {
      error.value = result.error || 'Login failed. Please check your credentials.'
    }
  } catch (err) {
    console.error('Login error:', err)
    error.value = 'Something went wrong. Please try again.'
  } finally {
    loading.value = false
  }
}

// Handle registration
async function handleRegister() {
  if (loading.value) return
  
  if (!validateRegistrationForm()) return

  loading.value = true

  try {
    const userData = {
      name: registerForm.name.trim(),
      email: registerForm.email.trim().toLowerCase(),
      password: registerForm.password
    }

    const result = await authService.register(userData)
    
    if (result.success) {
      successMessage.value = '🎉 Account created successfully! Setting up your profile...'
      
      setTimeout(() => {
        router.push('/preferences')
      }, 1500)
    } else {
      error.value = result.error || 'Registration failed. Please try again.'
      
      // Handle field-specific errors from backend
      if (result.errors) {
        Object.assign(errors, result.errors)
      }
    }
  } catch (err) {
    console.error('Registration error:', err)
    error.value = 'Something went wrong. Please try again.'
  } finally {
    loading.value = false
  }
}

// Auto-clear messages after 5 seconds
import { watch } from 'vue'

watch([error, successMessage], () => {
  if (error.value || successMessage.value) {
    setTimeout(() => {
      if (!loading.value) {
        error.value = ''
        successMessage.value = ''
      }
    }, 5000)
  }
})
</script>

<style scoped>
/* Transitions */
.slide-fade-enter-active, .slide-fade-leave-active {
  transition: all 0.3s ease-out;
}
.slide-fade-enter-from {
  transform: translateX(20px);
  opacity: 0;
}
.slide-fade-leave-to {
  transform: translateX(-20px);
  opacity: 0;
}

.error-fade-enter-active, .error-fade-leave-active {
  transition: all 0.2s ease-out;
}
.error-fade-enter-from, .error-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.alert-fade-enter-active, .alert-fade-leave-active {
  transition: all 0.3s ease-out;
}
.alert-fade-enter-from, .alert-fade-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}

/* Floating particles */
.floating-particles {
  position: absolute;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.particle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}

.particle:nth-child(1) {
  left: 10%;
  animation-duration: 6s;
}

.particle:nth-child(2) {
  left: 20%;
  animation-duration: 8s;
}

.particle:nth-child(3) {
  left: 30%;
  animation-duration: 5s;
}

.particle:nth-child(4) {
  left: 60%;
  animation-duration: 7s;
}

.particle:nth-child(5) {
  left: 80%;
  animation-duration: 9s;
}

.particle:nth-child(6) {
  left: 90%;
  animation-duration: 4s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(100vh) scale(0);
    opacity: 0;
  }
  10% {
    opacity: 1;
    transform: scale(1);
  }
  90% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Form enhancements */
.form-group input:focus {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Button hover effects */
button {
  position: relative;
  overflow: hidden;
}

button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  transition: left 0.5s;
}

button:hover::before {
  left: 100%;
}

/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}
</style>
