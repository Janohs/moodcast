<template>
  <div class="min-h-screen">
    <!-- Header -->
    <header class="bg-white/10 backdrop-blur-md border-b border-white/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-white">🌦️ MoodCast</h1>
          </div>
          
          <div class="flex items-center space-x-4">
            <span class="text-white/80">Welcome, {{ user?.name }}!</span>
            <button
              @click="logout"
              class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-200 rounded-lg transition-colors"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Section -->
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 mb-8">
        <h2 class="text-3xl font-bold text-white mb-4">Dashboard</h2>
        <p class="text-white/80 mb-6">Track your mood with the weather. Your personalized weather and mood tracking experience.</p>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white/10 rounded-lg p-4">
            <div class="text-2xl mb-2">📊</div>
            <div class="text-white font-semibold">Mood Entries</div>
            <div class="text-white/60 text-sm">Coming soon</div>
          </div>
          
          <div class="bg-white/10 rounded-lg p-4">
            <div class="text-2xl mb-2">🌡️</div>
            <div class="text-white font-semibold">Current Weather</div>
            <div v-if="currentWeather" class="text-white/80 text-sm">
              {{ currentWeather.current.temp_c }}°C in {{ currentWeather.location.name }}
            </div>
            <div v-else class="text-white/60 text-sm">Loading...</div>
          </div>
          
          <div class="bg-white/10 rounded-lg p-4">
            <div class="text-2xl mb-2">📈</div>
            <div class="text-white font-semibold">Insights</div>
            <div class="text-white/60 text-sm">Coming soon</div>
          </div>
        </div>
      </div>

      <!-- Current Weather -->
      <div v-if="currentWeather" class="bg-white/10 backdrop-blur-md rounded-2xl p-8 mb-8">
        <h3 class="text-xl font-semibold text-white mb-6">🌤️ Current Weather</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Location & Temperature -->
          <div class="bg-white/10 rounded-lg p-4">
            <div class="text-center">
              <div class="text-3xl font-bold text-white">{{ currentWeather.current.temp_c }}°C</div>
              <div class="text-white/80 text-sm">{{ currentWeather.location.name }}</div>
              <div class="text-white/60 text-xs">{{ currentWeather.location.region }}, {{ currentWeather.location.country }}</div>
            </div>
          </div>

          <!-- Condition -->
          <div class="bg-white/10 rounded-lg p-4">
            <div class="text-center">
              <img 
                :src="'https:' + currentWeather.current.condition.icon" 
                :alt="currentWeather.current.condition.text"
                class="w-12 h-12 mx-auto mb-2"
              >
              <div class="text-white font-medium">{{ currentWeather.current.condition.text }}</div>
              <div class="text-white/60 text-sm">Feels like {{ currentWeather.current.feelslike_c }}°C</div>
            </div>
          </div>

          <!-- Wind -->
          <div class="bg-white/10 rounded-lg p-4">
            <div class="text-center">
              <div class="text-2xl mb-2">💨</div>
              <div class="text-white font-medium">{{ currentWeather.current.wind_kph }} km/h</div>
              <div class="text-white/60 text-sm">{{ currentWeather.current.wind_dir }}</div>
            </div>
          </div>

          <!-- Humidity -->
          <div class="bg-white/10 rounded-lg p-4">
            <div class="text-center">
              <div class="text-2xl mb-2">💧</div>
              <div class="text-white font-medium">{{ currentWeather.current.humidity }}%</div>
              <div class="text-white/60 text-sm">Humidity</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Weather Preferences -->
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 mb-8">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-semibold text-white">Your Weather Preferences</h3>
          <button
            @click="editPreferences"
            class="px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-200 rounded-lg transition-colors"
          >
            Edit Preferences
          </button>
        </div>
        
        <div v-if="preferences && Object.keys(preferences).length > 0" class="space-y-4">
          <!-- Temperature -->
          <div class="bg-white/10 rounded-lg p-4">
            <h4 class="font-medium text-white mb-2">🌡️ Temperature</h4>
            <p class="text-white/80 text-sm">
              Ideal range: {{ preferences.temperature?.min }}°C - {{ preferences.temperature?.max }}°C
            </p>
            <p class="text-white/80 text-sm">
              Preference: {{ preferences.temperature?.preference || 'Not set' }}
            </p>
          </div>

          <!-- Liked Conditions -->
          <div v-if="preferences.conditions?.liked?.length > 0" class="bg-white/10 rounded-lg p-4">
            <h4 class="font-medium text-white mb-2">☀️ Enjoyed Weather</h4>
            <div class="flex flex-wrap gap-2">
              <span 
                v-for="condition in preferences.conditions.liked" 
                :key="condition"
                class="px-3 py-1 bg-green-500/20 text-green-200 rounded-full text-sm"
              >
                {{ formatCondition(condition) }}
              </span>
            </div>
          </div>

          <!-- Disliked Conditions -->
          <div v-if="preferences.conditions?.disliked?.length > 0" class="bg-white/10 rounded-lg p-4">
            <h4 class="font-medium text-white mb-2">⛈️ Challenging Weather</h4>
            <div class="flex flex-wrap gap-2">
              <span 
                v-for="condition in preferences.conditions.disliked" 
                :key="condition"
                class="px-3 py-1 bg-red-500/20 text-red-200 rounded-full text-sm"
              >
                {{ formatCondition(condition) }}
              </span>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-8">
          <p class="text-white/60 mb-4">No weather preferences set yet.</p>
          <button
            @click="editPreferences"
            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
          >
            Set Up Preferences
          </button>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8">
        <h3 class="text-xl font-semibold text-white mb-6">Quick Actions</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <button 
            @click="$router.push('/mood-entry')"
            class="p-4 bg-white/10 hover:bg-white/20 rounded-lg transition-colors text-left"
          >
            <div class="text-2xl mb-2">📝</div>
            <div class="text-white font-medium">Add Mood Entry</div>
            <div class="text-white/60 text-sm">Record how you're feeling today</div>
          </button>
          
          <button 
            @click="refreshWeatherData"
            class="p-4 bg-white/10 hover:bg-white/20 rounded-lg transition-colors text-left"
          >
            <div class="text-2xl mb-2">🌤️</div>
            <div class="text-white font-medium">Refresh Weather</div>
            <div class="text-white/60 text-sm">Update current conditions</div>
          </button>
          
          <button 
            @click="$router.push('/insights')"
            class="p-4 bg-white/10 hover:bg-white/20 rounded-lg transition-colors text-left"
          >
            <div class="text-2xl mb-2">📊</div>
            <div class="text-white font-medium">View Insights</div>
            <div class="text-white/60 text-sm">See your mood patterns</div>
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/authService.js'
import weatherService from '../services/weatherService.js'

const router = useRouter()

// State
const user = ref(null)
const preferences = ref({})
const currentWeather = ref(null)
const loading = ref(false)

// Methods
function logout() {
  authService.logout()
  router.push('/auth')
}

function editPreferences() {
  router.push('/preferences')
}

function formatCondition(condition) {
  return condition.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

function formatMoodSensitivity(sensitivity) {
  const map = {
    'high': 'Weather strongly affects my mood',
    'moderate': 'Weather moderately affects my mood',
    'low': 'Weather rarely affects my mood',
    'none': 'Weather doesn\'t affect my mood'
  }
  return map[sensitivity] || sensitivity
}

// Load user data
async function loadUserData() {
  try {
    user.value = authService.getUser()
    preferences.value = authService.getWeatherPreferences()
    
    // Optionally refresh from server
    const result = await authService.getCurrentUser()
    if (result.success) {
      user.value = result.data.user
      preferences.value = result.data.weather_preferences
    }
  } catch (error) {
    console.error('Failed to load user data:', error)
  }
}

// Load weather data
async function loadWeatherData() {
  try {
    loading.value = true
    // Try to get user's location or default to a major city
    const result = await weatherService.getCurrentWeather('auto:ip')
    if (result.success) {
      currentWeather.value = result.data
    } else {
      console.error('Failed to load weather:', result.error)
      // Fallback to London if geolocation fails
      const fallbackResult = await weatherService.getCurrentWeather('London')
      if (fallbackResult.success) {
        currentWeather.value = fallbackResult.data
      }
    }
  } catch (error) {
    console.error('Failed to load weather data:', error)
  } finally {
    loading.value = false
  }
}

// Refresh weather data
async function refreshWeatherData() {
  await loadWeatherData()
}

// Initialize
onMounted(() => {
  // Check if user is authenticated
  if (!authService.isAuthenticated()) {
    router.push('/auth')
    return
  }
  
  loadUserData()
  loadWeatherData()
})
</script>
