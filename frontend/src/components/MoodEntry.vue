<template>
  <div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="max-w-2xl w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">📝 How are you feeling?</h1>
        <p class="text-white/80">Track your mood and its connection to the weather</p>
      </div>

      <!-- Mood Entry Form -->
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 shadow-xl">
        <form @submit.prevent="submitMoodEntry">
          <!-- Current Weather Display -->
          <div v-if="currentWeather" class="bg-white/10 rounded-lg p-4 mb-6">
            <h3 class="text-white font-medium mb-2">🌤️ Current Weather</h3>
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <img 
                  :src="'https:' + currentWeather.current.condition.icon" 
                  :alt="currentWeather.current.condition.text"
                  class="w-10 h-10"
                >
                <div>
                  <div class="text-white font-medium">{{ currentWeather.current.temp_c }}°C</div>
                  <div class="text-white/60 text-sm">{{ currentWeather.current.condition.text }}</div>
                </div>
              </div>
              <div class="text-white/80 text-sm">{{ currentWeather.location.name }}</div>
            </div>
          </div>

          <!-- Emotion Selection -->
          <div class="mb-6">
            <label class="block text-white text-sm font-medium mb-3">How are you feeling right now?</label>
            <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
              <label v-for="emotion in emotionOptions" :key="emotion.value" class="cursor-pointer">
                <input
                  v-model="moodEntry.emotion"
                  type="radio"
                  :value="emotion.value"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  moodEntry.emotion === emotion.value 
                    ? `border-${emotion.color}-400 bg-${emotion.color}-500/20 text-white` 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">{{ emotion.emoji }}</div>
                  <div class="text-xs">{{ emotion.label }}</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Intensity Scale -->
          <div class="mb-6">
            <label class="block text-white text-sm font-medium mb-3">
              How intense is this feeling? ({{ moodEntry.intensity }}/5)
            </label>
            <div class="space-y-3">
              <input
                v-model.number="moodEntry.intensity"
                type="range"
                min="1"
                max="5"
                step="1"
                class="w-full h-2 bg-white/20 rounded-lg appearance-none cursor-pointer slider"
              />
              <div class="flex justify-between text-white/60 text-xs">
                <span>Very Low</span>
                <span>Low</span>
                <span>Moderate</span>
                <span>High</span>
                <span>Very High</span>
              </div>
            </div>
          </div>

          <!-- Weather Impact -->
          <div class="mb-6">
            <label class="block text-white text-sm font-medium mb-3">How does the current weather affect your mood?</label>
            <div class="grid grid-cols-3 gap-3">
              <label class="cursor-pointer">
                <input
                  v-model="moodEntry.weather_liked"
                  type="radio"
                  :value="true"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  moodEntry.weather_liked === true 
                    ? 'border-green-400 bg-green-500/20 text-white' 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">😊</div>
                  <div class="text-sm">Positive</div>
                </div>
              </label>
              
              <label class="cursor-pointer">
                <input
                  v-model="moodEntry.weather_liked"
                  type="radio"
                  :value="null"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  moodEntry.weather_liked === null 
                    ? 'border-gray-400 bg-gray-500/20 text-white' 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">😐</div>
                  <div class="text-sm">Neutral</div>
                </div>
              </label>
              
              <label class="cursor-pointer">
                <input
                  v-model="moodEntry.weather_liked"
                  type="radio"
                  :value="false"
                  class="sr-only"
                />
                <div :class="[
                  'p-4 rounded-lg border-2 text-center transition-all',
                  moodEntry.weather_liked === false 
                    ? 'border-red-400 bg-red-500/20 text-white' 
                    : 'border-white/30 bg-white/10 text-white/70 hover:border-white/50'
                ]">
                  <div class="text-2xl mb-1">😞</div>
                  <div class="text-sm">Negative</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-6">
            <label class="block text-white text-sm font-medium mb-3">Additional notes (optional)</label>
            <textarea
              v-model="moodEntry.notes"
              rows="3"
              class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
              placeholder="What's on your mind? Any specific thoughts about how the weather is affecting you today?"
            ></textarea>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-between items-center">
            <button
              type="button"
              @click="$router.push('/dashboard')"
              class="px-6 py-3 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-colors"
            >
              Cancel
            </button>
            
            <button
              type="submit"
              :disabled="!canSubmit || loading"
              class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
            >
              {{ loading ? 'Saving...' : 'Save Mood Entry' }}
            </button>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg">
            <p class="text-red-200 text-sm">{{ error }}</p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import emotionService from '../services/emotionService.js'
import weatherService from '../services/weatherService.js'
import authService from '../services/authService.js'

const router = useRouter()

// State
const loading = ref(false)
const error = ref('')
const currentWeather = ref(null)
const emotionOptions = ref(emotionService.getEmotionOptions())

// Form data
const moodEntry = reactive({
  emotion: '',
  intensity: 3,
  weather_liked: null,
  notes: ''
})

// Computed
const canSubmit = computed(() => {
  return moodEntry.emotion && moodEntry.intensity >= 1 && moodEntry.intensity <= 5
})

// Methods
async function loadCurrentWeather() {
  try {
    const result = await weatherService.getCurrentWeather('auto:ip')
    if (result.success) {
      currentWeather.value = result.data
    }
  } catch (err) {
    console.error('Failed to load weather:', err)
  }
}

async function submitMoodEntry() {
  if (!canSubmit.value || loading.value) return
  
  loading.value = true
  error.value = ''

  try {
    // Get current user
    const user = authService.getUser()
    if (!user) {
      throw new Error('User not authenticated')
    }

    // Prepare emotion data
    const emotionData = {
      user_id: user.id,
      emotion_type: moodEntry.emotion,
      intensity: moodEntry.intensity,
      weather_liked: moodEntry.weather_liked,
      notes: moodEntry.notes || null,
      weather_condition: currentWeather.value?.current?.condition?.text || null,
      temperature: currentWeather.value?.current?.temp_c || null,
      location: currentWeather.value?.location?.name || null
    }

    const result = await emotionService.createEmotion(emotionData)
    
    if (result.success) {
      // Redirect to dashboard with success message
      router.push('/dashboard?mood_saved=true')
    } else {
      error.value = result.error || 'Failed to save mood entry'
    }
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Initialize
onMounted(() => {
  // Check if user is authenticated
  if (!authService.isAuthenticated()) {
    router.push('/auth')
    return
  }
  
  loadCurrentWeather()
})
</script>

<style scoped>
/* Custom slider styles */
.slider::-webkit-slider-thumb {
  appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 0 2px 0 #555;
}

.slider::-moz-range-thumb {
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: none;
}
</style>
