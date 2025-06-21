<template>
  <DashboardWidget icon="🌤️" title="CURRENT WEATHER" :widget-class="'weather-widget'">
    <div v-if="weatherStore.isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <span>Loading weather...</span>
    </div>
    
    <div v-else-if="weatherStore.hasError" class="error-state">
      <span class="error-icon">⚠️</span>
      <span class="error-message">{{ weatherStore.error }}</span>
      <button @click="weatherStore.fetchCurrentWeather()" class="retry-btn">
        Retry
      </button>
    </div>
    
    <div v-else-if="weatherStore.currentWeather" class="weather-content">
      <div class="main-weather">
        <div class="weather-icon">
          <img :src="'https:' + weatherStore.currentIcon" :alt="weatherStore.currentCondition" />
        </div>
        <div class="weather-info">
          <div class="temperature">{{ Math.round(weatherStore.currentTemp) }}°C</div>
          <div class="condition">{{ weatherStore.currentCondition }}</div>
          <div class="location">📍 {{ weatherStore.locationName }}</div>
        </div>
      </div>
      
      <div class="weather-details">
        <div class="detail-item">
          <span class="detail-label">Feels like</span>
          <span class="detail-value">{{ Math.round(weatherStore.currentWeather.current.feelslike_c) }}°C</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Humidity</span>
          <span class="detail-value">{{ weatherStore.currentWeather.current.humidity }}%</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">UV Index</span>
          <span class="detail-value">{{ weatherStore.currentWeather.current.uv }}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Wind</span>
          <span class="detail-value">{{ weatherStore.currentWeather.current.wind_kph }} km/h</span>
        </div>
      </div>
    </div>
    
    <div v-else class="no-data">
      <span>No weather data available</span>
      <button @click="weatherStore.fetchCurrentWeather()" class="fetch-btn">
        Get Weather
      </button>
    </div>
  </DashboardWidget>
</template>

<script setup>
import { onMounted } from 'vue'
import { useWeatherStore } from '@/stores/weather'
import DashboardWidget from '@/components/common/DashboardWidget.vue'

const weatherStore = useWeatherStore()

onMounted(() => {
  if (!weatherStore.currentWeather) {
    weatherStore.fetchCurrentWeather()
  }
})
</script>

<style scoped>
.weather-widget {
  min-height: 200px;
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

.weather-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.main-weather {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.weather-icon img {
  width: 64px;
  height: 64px;
  object-fit: contain;
}

.weather-info {
  flex: 1;
}

.temperature {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.condition {
  font-size: 1.1rem;
  font-weight: 500;
  opacity: 0.9;
  margin-bottom: 0.5rem;
}

.location {
  font-size: 0.9rem;
  opacity: 0.7;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.weather-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.8rem;
  opacity: 0.7;
  font-weight: 500;
}

.detail-value {
  font-size: 1rem;
  font-weight: 600;
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
  .main-weather {
    flex-direction: column;
    text-align: center;
  }
  
  .weather-details {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
  
  .detail-item {
    flex-direction: row;
    justify-content: space-between;
  }
}
</style>
