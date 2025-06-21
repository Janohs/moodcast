<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-4xl font-bold text-white mb-2">🌦️ MoodCast</h1>
        <p class="text-white/80">Track your mood with the weather</p>
      </div>

      <!-- Auth Form -->
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 shadow-xl">
        <!-- Tab Toggle -->
        <div class="flex bg-white/20 rounded-lg p-1 mb-6">
          <button
            @click="isLogin = true"
            :class="[
              'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all',
              isLogin ? 'bg-white text-gray-900' : 'text-white hover:bg-white/10'
            ]"
          >
            Login
          </button>
          <button
            @click="isLogin = false"
            :class="[
              'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all',
              !isLogin ? 'bg-white text-gray-900' : 'text-white hover:bg-white/10'
            ]"
          >
            Sign Up
          </button>
        </div>

        <!-- Login Form -->
        <form v-if="isLogin" @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-white text-sm font-medium mb-2">Email</label>
            <input
              v-model="loginForm.email"
              type="email"
              required
              class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
              placeholder="Enter your email"
            />
          </div>
          
          <div>
            <label class="block text-white text-sm font-medium mb-2">Password</label>
            <input
              v-model="loginForm.password"
              type="password"
              required
              class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
              placeholder="Enter your password"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
          >
            {{ loading ? 'Signing In...' : 'Sign In' }}
          </button>
        </form>

        <!-- Register Form -->
        <form v-else @submit.prevent="handleRegister" class="space-y-4">
          <div>
            <label class="block text-white text-sm font-medium mb-2">Full Name</label>
            <input
              v-model="registerForm.name"
              type="text"
              required
              class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
              placeholder="Enter your full name"
            />
            <span v-if="errors.name" class="text-red-300 text-sm">{{ errors.name }}</span>
          </div>

          <div>
            <label class="block text-white text-sm font-medium mb-2">Email</label>
            <input
              v-model="registerForm.email"
              type="email"
              required
              class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
              placeholder="Enter your email"
            />
            <span v-if="errors.email" class="text-red-300 text-sm">{{ errors.email }}</span>
          </div>
          
          <div>
            <label class="block text-white text-sm font-medium mb-2">Password</label>
            <input
              v-model="registerForm.password"
              type="password"
              required
              class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
              placeholder="Create a password (min 6 characters)"
            />
            <span v-if="errors.password" class="text-red-300 text-sm">{{ errors.password }}</span>
          </div>

          <div>
            <label class="block text-white text-sm font-medium mb-2">Confirm Password</label>
            <input
              v-model="registerForm.confirmPassword"
              type="password"
              required
              class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
              placeholder="Confirm your password"
            />
            <span v-if="errors.confirmPassword" class="text-red-300 text-sm">{{ errors.confirmPassword }}</span>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
          >
            {{ loading ? 'Creating Account...' : 'Create Account' }}
          </button>
        </form>

        <!-- Error Message -->
        <div v-if="error" class="mt-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg">
          <p class="text-red-200 text-sm">{{ error }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="mt-4 p-3 bg-green-500/20 border border-green-500/50 rounded-lg">
          <p class="text-green-200 text-sm">{{ successMessage }}</p>
        </div>
      </div>

      <!-- Test Links -->
      <div class="text-center space-y-2">
        <p class="text-white/60 text-sm">For testing:</p>
        <div class="space-x-4">
          <router-link to="/database-test" class="text-blue-300 hover:text-blue-200 text-sm underline">
            Database Test
          </router-link>
          <router-link to="/weather-test" class="text-blue-300 hover:text-blue-200 text-sm underline">
            Weather Test
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/authService.js'

const router = useRouter()

// Reactive state
const isLogin = ref(true)
const loading = ref(false)
const error = ref('')
const successMessage = ref('')
const errors = reactive({})

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

// Handle login
async function handleLogin() {
  if (loading.value) return
  
  clearMessages()
  loading.value = true

  try {
    const result = await authService.login(loginForm.email, loginForm.password)
    
    if (result.success) {
      successMessage.value = 'Login successful! Redirecting...'
      
      // Check if user has weather preferences
      const preferences = authService.getWeatherPreferences()
      
      setTimeout(() => {
        if (Object.keys(preferences).length === 0) {
          // Redirect to preferences setup
          router.push('/preferences')
        } else {
          // Redirect to dashboard
          router.push('/dashboard')
        }
      }, 1000)
    } else {
      error.value = result.error
    }
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Handle registration
async function handleRegister() {
  if (loading.value) return
  
  clearMessages()
  
  // Client-side validation
  if (registerForm.password !== registerForm.confirmPassword) {
    errors.confirmPassword = 'Passwords do not match'
    return
  }
  
  if (registerForm.password.length < 6) {
    errors.password = 'Password must be at least 6 characters long'
    return
  }

  loading.value = true

  try {
    const userData = {
      name: registerForm.name,
      email: registerForm.email,
      password: registerForm.password
    }

    const result = await authService.register(userData)
    
    if (result.success) {
      successMessage.value = 'Account created successfully! Redirecting to setup...'
      
      setTimeout(() => {
        router.push('/preferences')
      }, 1000)
    } else {
      error.value = result.error
      
      // Handle field-specific errors
      if (result.errors) {
        Object.assign(errors, result.errors)
      }
    }
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Switch between login and register
function switchMode() {
  clearMessages()
  
  // Reset forms
  Object.assign(loginForm, { email: '', password: '' })
  Object.assign(registerForm, { name: '', email: '', password: '', confirmPassword: '' })
}

// Watch for mode changes to clear forms
import { watch } from 'vue'
watch(isLogin, switchMode)
</script>
