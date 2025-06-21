<template>
  <div class="min-h-screen">
    <!-- Header -->
    <header class="bg-white/10 backdrop-blur-md border-b border-white/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center">
            <button
              @click="$router.push('/dashboard')"
              class="mr-4 p-2 hover:bg-white/10 rounded-lg transition-colors"
            >
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <h1 class="text-2xl font-bold text-white">📊 Mood Insights</h1>
          </div>
          
          <div class="flex items-center space-x-4">
            <select 
              v-model="selectedPeriod" 
              @change="loadInsights"
              class="px-3 py-2 bg-white/20 border border-white/30 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
              <option value="7">Last 7 days</option>
              <option value="30">Last 30 days</option>
              <option value="90">Last 3 months</option>
            </select>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="text-white text-lg">Loading insights...</div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/20 border border-red-500/50 rounded-lg p-6">
        <h3 class="text-red-200 font-medium mb-2">Error Loading Insights</h3>
        <p class="text-red-200/80">{{ error }}</p>
        <button 
          @click="loadInsights" 
          class="mt-4 px-4 py-2 bg-red-500/30 hover:bg-red-500/40 text-red-200 rounded-lg transition-colors"
        >
          Try Again
        </button>
      </div>

      <!-- No Data State -->
      <div v-else-if="insights && insights.total_entries === 0" class="text-center py-12">
        <div class="text-6xl mb-4">📝</div>
        <h3 class="text-xl font-semibold text-white mb-2">No mood entries yet</h3>
        <p class="text-white/60 mb-6">Start tracking your mood to see insights and patterns</p>
        <button
          @click="$router.push('/mood-entry')"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
        >
          Add Your First Mood Entry
        </button>
      </div>

      <!-- Insights Content -->
      <div v-else-if="insights" class="space-y-8">
        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-white">{{ insights.total_entries }}</div>
              <div class="text-white/60 text-sm">Total Entries</div>
            </div>
          </div>
          
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-white">{{ insights.average_mood }}/5</div>
              <div class="text-white/60 text-sm">Average Mood</div>
            </div>
          </div>
          
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6">
            <div class="text-center">
              <div class="text-2xl mb-1">{{ getMostCommonEmotionEmoji(insights.emotion_distribution) }}</div>
              <div class="text-white font-medium">{{ getMostCommonEmotion(insights.emotion_distribution) }}</div>
              <div class="text-white/60 text-sm">Most Common</div>
            </div>
          </div>
          
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-white">{{ getWeatherImpactPercentage() }}%</div>
              <div class="text-white/60 text-sm">Weather Impact</div>
            </div>
          </div>
        </div>

        <!-- Mood Trends Chart -->
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8">
          <h3 class="text-xl font-semibold text-white mb-6">📈 Mood Trends</h3>
          <div v-if="Object.keys(insights.mood_trends).length > 0" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Simple trend visualization -->
              <div class="space-y-3">
                <h4 class="text-white font-medium">Daily Average Mood</h4>
                <div v-for="(mood, date) in getRecentTrends()" :key="date" class="flex items-center justify-between">
                  <div class="text-white/80 text-sm">{{ formatDate(date) }}</div>
                  <div class="flex items-center space-x-2">
                    <div class="w-32 bg-white/20 rounded-full h-2">
                      <div 
                        class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${(mood / 5) * 100}%` }"
                      ></div>
                    </div>
                    <div class="text-white font-medium w-12">{{ mood }}/5</div>
                  </div>
                </div>
              </div>
              
              <!-- Mood pattern summary -->
              <div class="space-y-3">
                <h4 class="text-white font-medium">Mood Pattern</h4>
                <div class="text-white/80 text-sm">
                  <p v-if="getTrendDirection() === 'improving'">
                    📈 Your mood has been <span class="text-green-300 font-medium">improving</span> recently
                  </p>
                  <p v-else-if="getTrendDirection() === 'declining'">
                    📉 Your mood has been <span class="text-red-300 font-medium">declining</span> recently
                  </p>
                  <p v-else>
                    📊 Your mood has been <span class="text-blue-300 font-medium">stable</span> recently
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-white/60">
            Not enough data to show trends yet. Keep tracking your mood!
          </div>
        </div>

        <!-- Emotion Distribution -->
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8">
          <h3 class="text-xl font-semibold text-white mb-6">🎭 Emotion Distribution</h3>
          <div v-if="insights.emotion_distribution && Object.keys(insights.emotion_distribution).length > 0" class="space-y-4">
            <div v-for="(count, emotion) in insights.emotion_distribution" :key="emotion" class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="text-2xl">{{ getEmotionEmoji(emotion) }}</div>
                <div class="text-white capitalize">{{ emotion }}</div>
              </div>
              <div class="flex items-center space-x-3">
                <div class="w-32 bg-white/20 rounded-full h-2">
                  <div 
                    class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${(count / insights.total_entries) * 100}%` }"
                  ></div>
                </div>
                <div class="text-white font-medium w-12">{{ count }}</div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-white/60">
            No emotion data available yet
          </div>
        </div>

        <!-- Weather Correlations -->
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8">
          <h3 class="text-xl font-semibold text-white mb-6">🌤️ Weather & Mood Connection</h3>
          <div v-if="insights.weather_correlations" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-green-500/20 rounded-lg p-4 text-center">
              <div class="text-2xl mb-2">😊</div>
              <div class="text-green-200 font-medium">{{ insights.weather_correlations.liked_weather_count }}</div>
              <div class="text-green-200/60 text-sm">Positive Weather Days</div>
            </div>
            
            <div class="bg-gray-500/20 rounded-lg p-4 text-center">
              <div class="text-2xl mb-2">😐</div>
              <div class="text-gray-200 font-medium">{{ insights.weather_correlations.neutral_weather_count }}</div>
              <div class="text-gray-200/60 text-sm">Neutral Weather Days</div>
            </div>
            
            <div class="bg-red-500/20 rounded-lg p-4 text-center">
              <div class="text-2xl mb-2">😞</div>
              <div class="text-red-200 font-medium">{{ insights.weather_correlations.disliked_weather_count }}</div>
              <div class="text-red-200/60 text-sm">Challenging Weather Days</div>
            </div>
          </div>
        </div>

        <!-- Recent Entries -->
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-white">📝 Recent Entries</h3>
            <button
              @click="$router.push('/mood-entry')"
              class="px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-200 rounded-lg transition-colors"
            >
              Add New Entry
            </button>
          </div>
          
          <div v-if="recentEntries.length > 0" class="space-y-4">
            <div v-for="entry in recentEntries.slice(0, 5)" :key="entry.id" class="bg-white/10 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="text-2xl">{{ getEmotionEmoji(entry.emotion_type) }}</div>
                  <div>
                    <div class="text-white font-medium capitalize">{{ entry.emotion_type }}</div>
                    <div class="text-white/60 text-sm">Intensity: {{ entry.intensity }}/5</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-white/80 text-sm">{{ formatDateTime(entry.created_at) }}</div>
                  <div v-if="entry.weather_liked !== null" class="text-white/60 text-xs">
                    Weather: {{ entry.weather_liked ? '😊 Positive' : '😞 Negative' }}
                  </div>
                </div>
              </div>
              <div v-if="entry.notes" class="mt-2 text-white/70 text-sm">
                "{{ entry.notes }}"
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-white/60">
            No recent entries found
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import emotionService from '../services/emotionService.js'
import authService from '../services/authService.js'

const router = useRouter()

// State
const loading = ref(false)
const error = ref('')
const insights = ref(null)
const recentEntries = ref([])
const selectedPeriod = ref(30)

// Computed
const emotionOptions = computed(() => emotionService.getEmotionOptions())

// Methods
async function loadInsights() {
  loading.value = true
  error.value = ''

  try {
    // Load insights and recent entries in parallel
    const [insightsResult, entriesResult] = await Promise.all([
      emotionService.getEmotionInsights(selectedPeriod.value),
      emotionService.getUserEmotions(10, selectedPeriod.value)
    ])

    if (insightsResult.success) {
      insights.value = insightsResult.data
    } else {
      throw new Error(insightsResult.error || 'Failed to load insights')
    }

    if (entriesResult.success) {
      recentEntries.value = entriesResult.data
    }

  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

function getMostCommonEmotion(distribution) {
  if (!distribution || Object.keys(distribution).length === 0) return 'None'
  
  const maxEmotion = Object.keys(distribution).reduce((a, b) => 
    distribution[a] > distribution[b] ? a : b
  )
  
  return maxEmotion.charAt(0).toUpperCase() + maxEmotion.slice(1)
}

function getMostCommonEmotionEmoji(distribution) {
  if (!distribution || Object.keys(distribution).length === 0) return '📊'
  
  const maxEmotion = Object.keys(distribution).reduce((a, b) => 
    distribution[a] > distribution[b] ? a : b
  )
  
  return getEmotionEmoji(maxEmotion)
}

function getEmotionEmoji(emotionType) {
  const option = emotionOptions.value.find(opt => opt.value === emotionType)
  return option?.emoji || '😐'
}

function getWeatherImpactPercentage() {
  if (!insights.value?.weather_correlations) return 0
  
  const total = insights.value.weather_correlations.liked_weather_count + 
                insights.value.weather_correlations.disliked_weather_count + 
                insights.value.weather_correlations.neutral_weather_count
  
  if (total === 0) return 0
  
  const impacted = insights.value.weather_correlations.liked_weather_count + 
                   insights.value.weather_correlations.disliked_weather_count
  
  return Math.round((impacted / total) * 100)
}

function getRecentTrends() {
  if (!insights.value?.mood_trends) return {}
  
  // Get last 7 days of trends
  const trends = insights.value.mood_trends
  const sortedDates = Object.keys(trends).sort().slice(-7)
  const recentTrends = {}
  
  sortedDates.forEach(date => {
    recentTrends[date] = trends[date]
  })
  
  return recentTrends
}

function getTrendDirection() {
  const trends = getRecentTrends()
  const values = Object.values(trends)
  
  if (values.length < 2) return 'stable'
  
  const first = values[0]
  const last = values[values.length - 1]
  const diff = last - first
  
  if (diff > 0.5) return 'improving'
  if (diff < -0.5) return 'declining'
  return 'stable'
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric' 
  })
}

function formatDateTime(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  })
}

// Initialize
onMounted(() => {
  // Check if user is authenticated
  if (!authService.isAuthenticated()) {
    router.push('/auth')
    return
  }
  
  loadInsights()
})
</script>
