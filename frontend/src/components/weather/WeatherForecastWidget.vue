<template>
  <DashboardWidget icon="📊" title="WEATHER FORECAST" :widget-class="'forecast-widget'">
    <div v-if="weatherStore.isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <span>Loading forecast...</span>
    </div>
    
    <div v-else-if="weatherStore.hasError" class="error-state">
      <span class="error-icon">⚠️</span>
      <span class="error-message">{{ weatherStore.error }}</span>
      <button @click="weatherStore.fetchForecast()" class="retry-btn">
        Retry
      </button>
    </div>
    
    <div v-else-if="weatherStore.forecast" class="forecast-content">
      <!-- Forecast Tabs -->
      <div class="forecast-tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab"
          @click="activeTab = tab"
          :class="['tab', { active: activeTab === tab }]"
        >
          {{ tab }}
        </button>
      </div>
      
      <!-- Hourly Forecast -->
      <div v-if="activeTab === 'Hourly'" class="hourly-forecast">
        <div class="forecast-timeline">
          <div 
            v-for="(hour, index) in weatherStore.hourlyForecast.slice(0, 8)" 
            :key="index"
            :class="['time-slot', { active: index === 0 }]"
          >
            <div class="time">{{ formatHour(hour.time) }}</div>
            <div class="weather-icon">
              <img :src="'https:' + hour.condition.icon" :alt="hour.condition.text" />
            </div>
            <div class="temp">{{ Math.round(hour.temp_c) }}°</div>
            <div class="chance-rain" v-if="hour.chance_of_rain > 0">
              💧{{ hour.chance_of_rain }}%
            </div>
          </div>
        </div>
      </div>
      
      <!-- Daily Forecast -->
      <div v-else class="daily-forecast">
        <div 
          v-for="(day, index) in weatherStore.dailyForecast" 
          :key="day.date"
          :class="['day-slot', { active: index === 0 }]"
        >
          <div class="day-info">
            <div class="day-name">{{ formatDay(day.date) }}</div>
            <div class="day-condition">{{ day.condition }}</div>
          </div>
          <div class="day-weather">
            <img :src="'https:' + day.icon" :alt="day.condition" class="day-icon" />
            <div class="day-temps">
              <span class="max-temp">{{ Math.round(day.maxTemp) }}°</span>
              <span class="min-temp">{{ Math.round(day.minTemp) }}°</span>
            </div>
          </div>
          <div class="day-details">
            <div class="detail" v-if="day.chanceOfRain > 0">
              💧 {{ day.chanceOfRain }}%
            </div>
            <div class="detail">
              💨 {{ day.humidity }}%
            </div>
            <div class="detail">
              ☀️ UV {{ day.uv }}
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else class="no-data">
      <span>No forecast data available</span>
      <button @click="weatherStore.fetchForecast()" class="fetch-btn">
        Get Forecast
      </button>
    </div>
  </DashboardWidget>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWeatherStore } from '@/stores/weather'
import DashboardWidget from '@/components/common/DashboardWidget.vue'

const weatherStore = useWeatherStore()
const activeTab = ref('Hourly')
const tabs = ['Hourly', 'Daily']

onMounted(() => {
  if (!weatherStore.forecast) {
    weatherStore.fetchForecast()
  }
})

function formatHour(timeString) {
  const date = new Date(timeString)
  const hour = date.getHours()
  
  if (hour === 0) return '12 AM'
  if (hour === 12) return '12 PM'
  if (hour < 12) return `${hour} AM`
  return `${hour - 12} PM`
}

function formatDay(dateString) {
  const date = new Date(dateString)
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  if (date.toDateString() === today.toDateString()) return 'Today'
  if (date.toDateString() === tomorrow.toDateString()) return 'Tomorrow'
  
  return date.toLocaleDateString('en-US', { weekday: 'short' })
}
</script>

<style scoped>
.forecast-widget {
  min-height: 300px;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 2rem 0;
}

.loading-spinner {
  width: 30px;
  height: 30px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top: 3px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 2rem 0;
  text-align: center;
}

.error-icon {
  font-size: 2rem;
}

.error-message {
  font-size: 0.9rem;
  opacity: 0.8;
}

.retry-btn, .fetch-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.retry-btn:hover, .fetch-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.forecast-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.forecast-tabs {
  display: flex;
  gap: 1rem;
}

.tab {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0.5rem 0;
  border-bottom: 2px solid transparent;
  transition: all 0.3s ease;
}

.tab.active {
  color: white;
  border-bottom-color: rgba(255, 255, 255, 0.8);
}

.hourly-forecast {
  overflow: hidden;
}

.forecast-timeline {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  padding: 0.5rem 0;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.forecast-timeline::-webkit-scrollbar {
  display: none;
}

.time-slot {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  min-width: 70px;
  padding: 1rem 0.5rem;
  border-radius: 12px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.time-slot.active {
  background: rgba(255, 255, 255, 0.1);
}

.time-slot:hover {
  background: rgba(255, 255, 255, 0.05);
}

.time {
  font-size: 0.8rem;
  opacity: 0.8;
  font-weight: 500;
}

.weather-icon img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.temp {
  font-size: 1rem;
  font-weight: 600;
}

.chance-rain {
  font-size: 0.7rem;
  opacity: 0.8;
}

.daily-forecast {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.day-slot {
  display: grid;
  grid-template-columns: 1fr auto auto;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.day-slot.active {
  background: rgba(255, 255, 255, 0.1);
}

.day-slot:hover {
  background: rgba(255, 255, 255, 0.05);
}

.day-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.day-name {
  font-size: 1rem;
  font-weight: 600;
}

.day-condition {
  font-size: 0.8rem;
  opacity: 0.7;
}

.day-weather {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.day-icon {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.day-temps {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.max-temp {
  font-size: 1rem;
  font-weight: 600;
}

.min-temp {
  font-size: 0.9rem;
  opacity: 0.7;
}

.day-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  align-items: flex-end;
}

.detail {
  font-size: 0.7rem;
  opacity: 0.8;
}

.no-data {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 2rem 0;
  text-align: center;
}

@media (max-width: 768px) {
  .day-slot {
    grid-template-columns: 1fr auto;
    gap: 0.5rem;
  }
  
  .day-details {
    grid-column: 1 / -1;
    flex-direction: row;
    justify-content: space-around;
    margin-top: 0.5rem;
  }
  
  .forecast-timeline {
    gap: 0.5rem;
  }
  
  .time-slot {
    min-width: 60px;
    gap: 0.25rem;
  }
}
</style>
