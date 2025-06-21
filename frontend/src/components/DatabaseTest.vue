<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Database Test</h1>
    
    <!-- Authentication Section -->
    <div class="mb-8 p-4 bg-gray-100 rounded-lg">
      <h2 class="text-xl font-semibold mb-4">Authentication</h2>
      <div v-if="!user" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <input 
            v-model="email" 
            type="email" 
            placeholder="Email"
            class="px-3 py-2 border rounded-md"
          />
          <input 
            v-model="password" 
            type="password" 
            placeholder="Password"
            class="px-3 py-2 border rounded-md"
          />
        </div>
        <div class="space-x-2">
          <button 
            @click="signIn"
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
          >
            Sign In
          </button>
          <button 
            @click="signUp"
            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
          >
            Sign Up
          </button>
        </div>
      </div>
      <div v-else class="space-y-2">
        <p class="text-green-600">Signed in as: {{ user.email }}</p>
        <button 
          @click="signOut"
          class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
        >
          Sign Out
        </button>
      </div>
    </div>

    <!-- Weather Logs Section -->
    <div class="mb-8 p-4 bg-blue-50 rounded-lg">
      <h2 class="text-xl font-semibold mb-4">Weather Logs</h2>
      <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <input 
            v-model="newWeatherLog.location" 
            placeholder="Location"
            class="px-3 py-2 border rounded-md"
          />
          <input 
            v-model="newWeatherLog.temperature" 
            type="number" 
            placeholder="Temperature"
            class="px-3 py-2 border rounded-md"
          />
          <input 
            v-model="newWeatherLog.weather_condition" 
            placeholder="Weather Condition"
            class="px-3 py-2 border rounded-md"
          />
        </div>
        <button 
          @click="addWeatherLog"
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
        >
          Add Weather Log
        </button>
      </div>
      
      <div v-if="weatherLogs.length > 0" class="mt-4">
        <h3 class="font-semibold mb-2">Recent Weather Logs:</h3>
        <div class="space-y-2">
          <div 
            v-for="log in weatherLogs" 
            :key="log.id"
            class="p-3 bg-white rounded-md border"
          >
            <p><strong>{{ log.location }}</strong> - {{ log.temperature }}°C</p>
            <p>{{ log.weather_condition }} - {{ log.weather_description }}</p>
            <p class="text-sm text-gray-600">{{ new Date(log.recorded_at).toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Emotions Section -->
    <div v-if="user" class="mb-8 p-4 bg-green-50 rounded-lg">
      <h2 class="text-xl font-semibold mb-4">Emotions</h2>
      <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <select 
            v-model="newEmotion.emotion_type"
            class="px-3 py-2 border rounded-md"
          >
            <option value="">Select Emotion</option>
            <option value="happy">Happy</option>
            <option value="sad">Sad</option>
            <option value="anxious">Anxious</option>
            <option value="calm">Calm</option>
            <option value="energetic">Energetic</option>
            <option value="tired">Tired</option>
          </select>
          <input 
            v-model="newEmotion.intensity" 
            type="number" 
            min="1" 
            max="10" 
            placeholder="Intensity (1-10)"
            class="px-3 py-2 border rounded-md"
          />
          <select 
            v-model="newEmotion.weather_liked"
            class="px-3 py-2 border rounded-md"
          >
            <option value="">Weather Opinion</option>
            <option :value="true">Liked the weather</option>
            <option :value="false">Disliked the weather</option>
          </select>
        </div>
        <textarea 
          v-model="newEmotion.notes" 
          placeholder="Notes (optional)"
          class="w-full px-3 py-2 border rounded-md"
          rows="3"
        ></textarea>
        <button 
          @click="addEmotion"
          class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
        >
          Add Emotion
        </button>
      </div>
      
      <div v-if="emotions.length > 0" class="mt-4">
        <h3 class="font-semibold mb-2">Your Emotions:</h3>
        <div class="space-y-2">
          <div 
            v-for="emotion in emotions" 
            :key="emotion.id"
            class="p-3 bg-white rounded-md border"
          >
            <p><strong>{{ emotion.emotion_type }}</strong> ({{ emotion.intensity }}/10)</p>
            <p v-if="emotion.notes">{{ emotion.notes }}</p>
            <p v-if="emotion.weather_liked !== null" class="text-sm">
              Weather: {{ emotion.weather_liked ? '👍 Liked' : '👎 Disliked' }}
            </p>
            <p class="text-sm text-gray-600">{{ new Date(emotion.created_at).toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Messages -->
    <div v-if="error" class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { authService, weatherService, emotionService } from '../utils/database.js'

// Reactive data
const user = ref(null)
const email = ref('')
const password = ref('')
const error = ref('')
const weatherLogs = ref([])
const emotions = ref([])

// New entries
const newWeatherLog = ref({
  location: '',
  temperature: '',
  weather_condition: '',
  weather_description: '',
  recorded_at: new Date().toISOString()
})

const newEmotion = ref({
  emotion_type: '',
  intensity: '',
  notes: '',
  weather_liked: null
})

// Authentication methods
async function signIn() {
  try {
    const { data, error: authError } = await authService.signIn(email.value, password.value)
    if (authError) throw authError
    
    user.value = data.user
    await loadUserData()
    error.value = ''
  } catch (err) {
    error.value = err.message
  }
}

async function signUp() {
  try {
    const { data, error: authError } = await authService.signUp(email.value, password.value)
    if (authError) throw authError
    
    error.value = 'Check your email for verification link!'
  } catch (err) {
    error.value = err.message
  }
}

async function signOut() {
  try {
    await authService.signOut()
    user.value = null
    emotions.value = []
    error.value = ''
  } catch (err) {
    error.value = err.message
  }
}

// Data loading methods
async function loadWeatherLogs() {
  try {
    const { data, error: weatherError } = await weatherService.getWeatherLogs('Test Location', 5)
    if (weatherError) throw weatherError
    
    weatherLogs.value = data || []
  } catch (err) {
    error.value = err.message
  }
}

async function loadUserEmotions() {
  if (!user.value) return
  
  try {
    const { data, error: emotionError } = await emotionService.getUserEmotions(10)
    if (emotionError) throw emotionError
    
    emotions.value = data || []
  } catch (err) {
    error.value = err.message
  }
}

async function loadUserData() {
  await Promise.all([
    loadWeatherLogs(),
    loadUserEmotions()
  ])
}

// Add new entries
async function addWeatherLog() {
  try {
    const logData = {
      ...newWeatherLog.value,
      temperature: parseFloat(newWeatherLog.value.temperature),
      recorded_at: new Date().toISOString()
    }
    
    const { data, error: weatherError } = await weatherService.saveWeatherLog(logData)
    if (weatherError) throw weatherError
    
    // Reset form
    newWeatherLog.value = {
      location: '',
      temperature: '',
      weather_condition: '',
      weather_description: '',
      recorded_at: new Date().toISOString()
    }
    
    // Reload weather logs
    await loadWeatherLogs()
    error.value = ''
  } catch (err) {
    error.value = err.message
  }
}

async function addEmotion() {
  if (!user.value) {
    error.value = 'Please sign in to add emotions'
    return
  }
  
  try {
    const emotionData = {
      ...newEmotion.value,
      intensity: parseInt(newEmotion.value.intensity)
    }
    
    const { data, error: emotionError } = await emotionService.saveEmotion(emotionData)
    if (emotionError) throw emotionError
    
    // Reset form
    newEmotion.value = {
      emotion_type: '',
      intensity: '',
      notes: '',
      weather_liked: null
    }
    
    // Reload emotions
    await loadUserEmotions()
    error.value = ''
  } catch (err) {
    error.value = err.message
  }
}

// Initialize
onMounted(async () => {
  // Check for existing session
  const { data } = await authService.getSession()
  if (data.session) {
    user.value = data.session.user
    await loadUserData()
  }
  
  // Listen for auth changes
  authService.onAuthStateChange((event, session) => {
    if (session) {
      user.value = session.user
      loadUserData()
    } else {
      user.value = null
      emotions.value = []
    }
  })
  
  // Load initial weather logs (these are public)
  await loadWeatherLogs()
})
</script>
