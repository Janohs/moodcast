<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">🌦️ Integrated Weather Service Test</h1>
    
    <!-- Location Input -->
    <div class="mb-6 p-4 bg-gray-100 rounded-lg">
      <h2 class="text-xl font-semibold mb-4">Get Weather Data</h2>
      <div class="flex gap-4 mb-4">
        <input 
          v-model="location" 
          type="text" 
          placeholder="Enter location (e.g., London, New York) or leave empty for auto-detection"
          class="flex-1 px-3 py-2 border rounded-md"
          @keyup.enter="getWeather"
        />
        <button 
          @click="getWeather()"
          :disabled="loading"
          class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50"
        >
          {{ loading ? 'Loading...' : 'Get Weather' }}
        </button>
        <button 
          @click="getWeather(true)"
          :disabled="loading"
          class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50"
        >
          Force Refresh
        </button>
      </div>
      
      <div class="flex gap-2 mb-4">
        <button 
          @click="getWeatherHistory()"
          class="px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600"
        >
          View History
        </button>
        <button 
          @click="getForecast()"
          class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600"
        >
          Get Forecast
        </button>
      </div>
    </div>

    <!-- Weather Data Display -->
    <div v-if="weatherData" class="mb-6 p-4 bg-blue-50 rounded-lg">
      <div class="flex justify-between items-start mb-4">
        <h2 class="text-xl font-semibold">Current Weather</h2>
        <span class="px-3 py-1 rounded-full text-sm font-medium"
              :class="{
                'bg-green-100 text-green-800': weatherData.source === 'api',
                'bg-yellow-100 text-yellow-800': weatherData.source === 'cache',
                'bg-red-100 text-red-800': weatherData.source === 'fallback'
              }">
          {{ weatherData.source === 'api' ? '🌐 Fresh from API' : 
             weatherData.source === 'cache' ? '📦 From Cache' : 
             '🔄 Fallback Data' }}
        </span>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Location Info -->
        <div class="bg-white p-4 rounded-lg">
          <h3 class="font-semibold mb-2">📍 Location</h3>
          <p><strong>{{ weatherData.data.location.name }}</strong></p>
          <p>{{ weatherData.data.location.region }}, {{ weatherData.data.location.country }}</p>
          <p class="text-sm text-gray-600">
            Lat: {{ weatherData.data.location.lat }}, 
            Lon: {{ weatherData.data.location.lon }}
          </p>
          <p class="text-sm text-gray-600">{{ weatherData.data.location.localtime }}</p>
        </div>

        <!-- Current Conditions -->
        <div class="bg-white p-4 rounded-lg">
          <h3 class="font-semibold mb-2">🌡️ Current Conditions</h3>
          <div class="flex items-center mb-2">
            <img v-if="weatherData.data.current.condition.icon" 
                 :src="weatherData.data.current.condition.icon" 
                 :alt="weatherData.data.current.condition.text"
                 class="w-12 h-12 mr-3">
            <div>
              <p class="text-2xl font-bold">{{ weatherData.data.current.temp_c }}°C</p>
              <p class="text-gray-600">{{ weatherData.data.current.condition.text }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <p>Feels like: {{ weatherData.data.current.feelslike_c }}°C</p>
            <p>Humidity: {{ weatherData.data.current.humidity }}%</p>
            <p>Wind: {{ weatherData.data.current.wind_kph }} km/h {{ weatherData.data.current.wind_dir }}</p>
            <p>Pressure: {{ weatherData.data.current.pressure_mb }} mb</p>
            <p>UV Index: {{ weatherData.data.current.uv }}</p>
            <p v-if="weatherData.data.current.vis_km">Visibility: {{ weatherData.data.current.vis_km }} km</p>
          </div>
        </div>
      </div>

      <!-- Cache Status -->
      <div v-if="weatherData.cached !== undefined" class="mt-4 p-3 bg-white rounded-lg">
        <p class="text-sm">
          <span class="font-medium">Database Status:</span>
          {{ weatherData.cached ? '✅ Data saved to database' : '⚠️ Failed to cache data' }}
          {{ weatherData.warning ? ` (${weatherData.warning})` : '' }}
        </p>
      </div>
    </div>

    <!-- Weather History -->
    <div v-if="historyData && historyData.length > 0" class="mb-6 p-4 bg-green-50 rounded-lg">
      <h2 class="text-xl font-semibold mb-4">📊 Weather History</h2>
      <div class="space-y-2 max-h-60 overflow-y-auto">
        <div 
          v-for="record in historyData" 
          :key="record.id"
          class="p-3 bg-white rounded-md border"
        >
          <div class="flex justify-between items-start">
            <div>
              <p><strong>{{ record.location }}</strong></p>
              <p>{{ record.temperature }}°C - {{ record.weather_condition }}</p>
              <p class="text-sm text-gray-600">{{ record.weather_description }}</p>
            </div>
            <div class="text-right text-sm text-gray-600">
              <p>{{ new Date(record.recorded_at).toLocaleDateString() }}</p>
              <p>{{ new Date(record.recorded_at).toLocaleTimeString() }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Forecast Data -->
    <div v-if="forecastData" class="mb-6 p-4 bg-orange-50 rounded-lg">
      <h2 class="text-xl font-semibold mb-4">🔮 Weather Forecast</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div 
          v-for="day in forecastData.data.forecast.forecastday" 
          :key="day.date"
          class="p-3 bg-white rounded-lg"
        >
          <p class="font-semibold">{{ new Date(day.date).toLocaleDateString() }}</p>
          <div class="flex items-center my-2">
            <img :src="day.day.condition.icon" :alt="day.day.condition.text" class="w-8 h-8 mr-2">
            <div>
              <p class="text-lg font-bold">{{ day.day.maxtemp_c }}° / {{ day.day.mintemp_c }}°</p>
              <p class="text-sm text-gray-600">{{ day.day.condition.text }}</p>
            </div>
          </div>
          <div class="text-xs text-gray-600">
            <p>Rain: {{ day.day.daily_chance_of_rain }}%</p>
            <p>UV: {{ day.day.uv }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
      <h3 class="font-semibold">Error:</h3>
      <p>{{ error }}</p>
    </div>

    <!-- Success Messages -->
    <div v-if="successMessage" class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
      {{ successMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import weatherAPIService from '../services/weatherService.js'

// Reactive data
const location = ref('')
const loading = ref(false)
const weatherData = ref(null)
const historyData = ref([])
const forecastData = ref(null)
const error = ref('')
const successMessage = ref('')

// Clear messages
function clearMessages() {
  error.value = ''
  successMessage.value = ''
}

// Get current weather
async function getWeather(forceRefresh = false) {
  if (loading.value) return
  
  clearMessages()
  loading.value = true
  
  try {
    const locationQuery = location.value.trim() || 'auto:ip'
    const result = await weatherAPIService.getCurrentWeather(locationQuery, forceRefresh)
    
    if (result.success) {
      weatherData.value = result
      successMessage.value = `Weather data loaded successfully! (Source: ${result.source})`
      
      // Auto-load history for this location if we have a specific location
      if (location.value.trim()) {
        await getWeatherHistory()
      }
    } else {
      error.value = result.error
      weatherData.value = null
    }
  } catch (err) {
    error.value = err.message
    weatherData.value = null
  } finally {
    loading.value = false
  }
}

// Get weather history
async function getWeatherHistory() {
  clearMessages()
  
  try {
    const locationQuery = location.value.trim() || weatherData.value?.data?.location?.name || 'auto:ip'
    const result = await weatherAPIService.getWeatherHistory(locationQuery)
    
    if (result.success) {
      historyData.value = result.data
      successMessage.value = `Found ${result.count} historical weather records`
    } else {
      error.value = result.error
      historyData.value = []
    }
  } catch (err) {
    error.value = err.message
    historyData.value = []
  }
}

// Get forecast
async function getForecast() {
  clearMessages()
  
  try {
    const locationQuery = location.value.trim() || weatherData.value?.data?.location?.name || 'auto:ip'
    const result = await weatherAPIService.getForecast(locationQuery, 3)
    
    if (result.status === 'success') {
      forecastData.value = result
      successMessage.value = 'Forecast loaded successfully!'
    } else {
      error.value = result.message || 'Failed to load forecast'
      forecastData.value = null
    }
  } catch (err) {
    error.value = err.message
    forecastData.value = null
  }
}
</script>
