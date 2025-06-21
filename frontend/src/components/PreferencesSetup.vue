<template>
  <div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="max-w-2xl w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">🌦️ Weather Preferences</h1>
        <p class="text-white/80">Tell us about your ideal weather conditions</p>
      </div>

      <!-- Progress Indicator -->
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 shadow-xl">
        <div class="mb-8">
          <div class="flex justify-between text-sm text-white/60 mb-2">
            <span>Step {{ currentStep }} of {{ totalSteps }}</span>
            <span>{{ Math.round((currentStep / totalSteps) * 100) }}% Complete</span>
          </div>
          <div class="bg-white/20 rounded-full h-2">
            <div 
              class="bg-blue-500 rounded-full h-2 transition-all duration-300"
              :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
            ></div>
          </div>
        </div>

        <!-- Step 1: Temperature Preferences -->
        <div v-if="currentStep === 1" class="space-y-6">
          <h2 class="text-2xl font-semibold text-white mb-4">🌡️ Temperature Preferences</h2>
          
          <div>
            <label class="block text-white text-sm font-medium mb-3">What's your ideal temperature range?</label>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-white/80 text-xs mb-1">Minimum (°C)</label>
                <input
                  v-model.number="preferences.temperature.min"
                  type="number"
                  min="-20"
                  max="50"
                  class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400"
                  placeholder="15"
                />
              </div>
              <div>
                <label class="block text-white/80 text-xs mb-1">Maximum (°C)</label>
                <input
                  v-model.number="preferences.temperature.max"
                  type="number"
                  min="-20"
                  max="50"
                  class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400"
                  placeholder="25"
                />
              </div>
            </div>
          </div>

          <div>
            <label class="block text-white text-sm font-medium mb-3">Temperature preference</label>
            <div class="grid grid-cols-3 gap-3">
              <label class="cursor-pointer">
                <input
                  v-model="preferences.temperature.preference"
                  type="radio"
                  value="cold"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  preferences.temperature.preference === 'cold' 
                    ? 'border-blue-400 bg-blue-500/20 text-white' 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">🥶</div>
                  <div class="text-sm">Cold</div>
                </div>
              </label>
              
              <label class="cursor-pointer">
                <input
                  v-model="preferences.temperature.preference"
                  type="radio"
                  value="moderate"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  preferences.temperature.preference === 'moderate' 
                    ? 'border-blue-400 bg-blue-500/20 text-white' 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">😊</div>
                  <div class="text-sm">Moderate</div>
                </div>
              </label>
              
              <label class="cursor-pointer">
                <input
                  v-model="preferences.temperature.preference"
                  type="radio"
                  value="warm"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  preferences.temperature.preference === 'warm' 
                    ? 'border-blue-400 bg-blue-500/20 text-white' 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">🔥</div>
                  <div class="text-sm">Warm</div>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- Step 2: Weather Conditions -->
        <div v-if="currentStep === 2" class="space-y-6">
          <h2 class="text-2xl font-semibold text-white mb-4">☀️ Weather Conditions</h2>
          
          <div>
            <label class="block text-white text-sm font-medium mb-3">Which weather conditions do you enjoy? (Select all that apply)</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <label v-for="condition in weatherConditions" :key="condition.value" class="cursor-pointer">
                <input
                  v-model="preferences.conditions.liked"
                  type="checkbox"
                  :value="condition.value"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  preferences.conditions.liked.includes(condition.value)
                    ? 'border-green-400 bg-green-500/20 text-white' 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">{{ condition.emoji }}</div>
                  <div class="text-sm">{{ condition.label }}</div>
                </div>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-white text-sm font-medium mb-3">Which weather conditions affect your mood negatively? (Optional)</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <label v-for="condition in weatherConditions" :key="condition.value" class="cursor-pointer">
                <input
                  v-model="preferences.conditions.disliked"
                  type="checkbox"
                  :value="condition.value"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  preferences.conditions.disliked.includes(condition.value)
                    ? 'border-red-400 bg-red-500/20 text-white' 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">{{ condition.emoji }}</div>
                  <div class="text-sm">{{ condition.label }}</div>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-8">
          <button
            v-if="currentStep > 1"
            @click="currentStep--"
            class="px-6 py-3 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-colors"
          >
            Previous
          </button>
          <div v-else></div>

          <button
            v-if="currentStep < totalSteps"
            @click="nextStep"
            :disabled="!canProceed"
            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
          >
            Next
          </button>
          
          <button
            v-else
            @click="savePreferences"
            :disabled="loading || !canProceed"
            class="px-6 py-3 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
          >
            {{ loading ? 'Saving...' : 'Complete Setup' }}
          </button>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mt-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg">
          <p class="text-red-200 text-sm">{{ error }}</p>
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

// State
const currentStep = ref(1)
const totalSteps = ref(2)
const loading = ref(false)
const error = ref('')

// Form data
const preferences = reactive({
  temperature: {
    min: 18,
    max: 25,
    preference: 'moderate'
  },
  conditions: {
    liked: [],
    disliked: []
  }
})

// Options
const weatherConditions = [
  { value: 'sunny', label: 'Sunny', emoji: '☀️' },
  { value: 'partly_cloudy', label: 'Partly Cloudy', emoji: '⛅' },
  { value: 'cloudy', label: 'Cloudy', emoji: '☁️' },
  { value: 'rainy', label: 'Rainy', emoji: '🌧️' },
  { value: 'thunderstorm', label: 'Thunderstorm', emoji: '⛈️' },
  { value: 'snowy', label: 'Snowy', emoji: '❄️' },
  { value: 'foggy', label: 'Foggy', emoji: '🌫️' },
  { value: 'windy', label: 'Windy', emoji: '💨' }
]

// Computed
const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1:
      return preferences.temperature.min !== '' && 
             preferences.temperature.max !== '' && 
             preferences.temperature.preference !== '' &&
             preferences.temperature.min < preferences.temperature.max
    case 2:
      return preferences.conditions.liked.length > 0
    default:
      return true
  }
})

// Methods
function nextStep() {
  if (canProceed.value) {
    currentStep.value++
  }
}

async function savePreferences() {
  if (loading.value || !canProceed.value) return
  
  loading.value = true
  error.value = ''

  try {
    const result = await authService.updateWeatherPreferences(preferences)
    
    if (result.success) {
      // Redirect to dashboard
      router.push('/dashboard')
    } else {
      error.value = result.error || 'Failed to save preferences'
    }
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Load existing preferences if any
async function loadExistingPreferences() {
  try {
    const existingPrefs = authService.getWeatherPreferences()
    if (existingPrefs && Object.keys(existingPrefs).length > 0) {
      Object.assign(preferences, existingPrefs)
    }
  } catch (err) {
    console.warn('Could not load existing preferences:', err)
  }
}

// Initialize
loadExistingPreferences()
</script>
