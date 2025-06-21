<template>
  <DashboardWidget icon="🧠" title="WEATHER MOOD INSIGHT" :widget-class="'mood-weather-widget'">
    <div v-if="weatherStore.isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <span>Analyzing weather mood...</span>
    </div>
    
    <div v-else-if="weatherStore.hasError" class="error-state">
      <span class="error-icon">⚠️</span>
      <span class="error-message">Unable to analyze mood</span>
    </div>
    
    <div v-else-if="weatherStore.moodSuggestion" class="mood-content">
      <!-- Mood Score Section -->
      <div class="mood-scores">
        <div class="mood-score">
          <div class="score-circle">
            <svg viewBox="0 0 36 36" class="score-svg">
              <path
                class="score-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                class="score-fill"
                :stroke-dasharray="`${weatherStore.moodSuggestion.happiness}, 100`"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="score-text">
              <div class="score-value">{{ weatherStore.moodSuggestion.happiness }}</div>
              <div class="score-label">Happiness</div>
            </div>
          </div>
        </div>
        
        <div class="mood-score">
          <div class="score-circle">
            <svg viewBox="0 0 36 36" class="score-svg">
              <path
                class="score-bg"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                class="score-fill energy"
                :stroke-dasharray="`${weatherStore.moodSuggestion.energy}, 100`"
                d="M18 2.0845
                  a 15.9155 15.9155 0 0 1 0 31.831
                  a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="score-text">
              <div class="score-value">{{ weatherStore.moodSuggestion.energy }}</div>
              <div class="score-label">Energy</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Current Mood Display -->
      <div class="current-mood">
        <div class="mood-emoji">{{ getMoodEmoji(weatherStore.moodSuggestion.mood) }}</div>
        <div class="mood-info">
          <div class="mood-name">{{ capitalize(weatherStore.moodSuggestion.mood) }}</div>
          <div class="mood-description">Based on current weather conditions</div>
        </div>
      </div>
      
      <!-- Weather Factors -->
      <div class="weather-factors">
        <div class="factor-title">Weather Impact Factors</div>
        <div class="factors-grid">
          <div class="factor-item">
            <span class="factor-icon">🌡️</span>
            <span class="factor-label">Temperature</span>
            <span class="factor-value">{{ Math.round(weatherStore.currentTemp) }}°C</span>
            <span class="factor-impact" :class="getTemperatureImpact(weatherStore.currentTemp)">
              {{ getTemperatureImpactText(weatherStore.currentTemp) }}
            </span>
          </div>
          
          <div class="factor-item" v-if="weatherStore.currentWeather">
            <span class="factor-icon">💧</span>
            <span class="factor-label">Humidity</span>
            <span class="factor-value">{{ weatherStore.currentWeather.current.humidity }}%</span>
            <span class="factor-impact" :class="getHumidityImpact(weatherStore.currentWeather.current.humidity)">
              {{ getHumidityImpactText(weatherStore.currentWeather.current.humidity) }}
            </span>
          </div>
          
          <div class="factor-item" v-if="weatherStore.currentWeather">
            <span class="factor-icon">☀️</span>
            <span class="factor-label">UV Index</span>
            <span class="factor-value">{{ weatherStore.currentWeather.current.uv }}</span>
            <span class="factor-impact" :class="getUVImpact(weatherStore.currentWeather.current.uv)">
              {{ getUVImpactText(weatherStore.currentWeather.current.uv) }}
            </span>
          </div>
          
          <div class="factor-item">
            <span class="factor-icon">🌤️</span>
            <span class="factor-label">Condition</span>
            <span class="factor-value">{{ weatherStore.currentCondition }}</span>
            <span class="factor-impact" :class="getConditionImpact(weatherStore.currentCondition)">
              {{ getConditionImpactText(weatherStore.currentCondition) }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- Recommendation -->
      <div class="recommendation">
        <div class="recommendation-icon">💡</div>
        <div class="recommendation-text">{{ weatherStore.moodSuggestion.recommendation }}</div>
      </div>
    </div>
    
    <div v-else class="no-data">
      <span>No weather data for mood analysis</span>
    </div>
  </DashboardWidget>
</template>

<script setup>
import { useWeatherStore } from '@/stores/weather'
import DashboardWidget from '@/components/common/DashboardWidget.vue'

const weatherStore = useWeatherStore()

function getMoodEmoji(mood) {
  const emojis = {
    happy: '😊',
    calm: '😌',
    energetic: '⚡',
    peaceful: '🕊️',
    neutral: '😐'
  }
  return emojis[mood] || '😐'
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

function getTemperatureImpact(temp) {
  if (temp >= 20 && temp <= 25) return 'positive'
  if (temp < 10 || temp > 30) return 'negative'
  return 'neutral'
}

function getTemperatureImpactText(temp) {
  if (temp >= 20 && temp <= 25) return 'Optimal'
  if (temp < 10) return 'Cold'
  if (temp > 30) return 'Hot'
  return 'Moderate'
}

function getHumidityImpact(humidity) {
  if (humidity > 80) return 'negative'
  if (humidity < 30) return 'negative'
  return 'neutral'
}

function getHumidityImpactText(humidity) {
  if (humidity > 80) return 'High'
  if (humidity < 30) return 'Low'
  return 'Good'
}

function getUVImpact(uv) {
  if (uv > 6) return 'positive'
  if (uv < 2) return 'neutral'
  return 'neutral'
}

function getUVImpactText(uv) {
  if (uv > 6) return 'Energizing'
  if (uv < 2) return 'Low'
  return 'Moderate'
}

function getConditionImpact(condition) {
  const conditionLower = condition.toLowerCase()
  if (conditionLower.includes('sunny') || conditionLower.includes('clear')) return 'positive'
  if (conditionLower.includes('rain') || conditionLower.includes('storm')) return 'negative'
  return 'neutral'
}

function getConditionImpactText(condition) {
  const conditionLower = condition.toLowerCase()
  if (conditionLower.includes('sunny') || conditionLower.includes('clear')) return 'Uplifting'
  if (conditionLower.includes('rain') || conditionLower.includes('storm')) return 'Calming'
  return 'Neutral'
}
</script>

<style scoped>
.mood-weather-widget {
  min-height: 400px;
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

.mood-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.mood-scores {
  display: flex;
  justify-content: space-around;
  gap: 2rem;
}

.mood-score {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.score-circle {
  position: relative;
  width: 80px;
  height: 80px;
}

.score-svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.score-bg {
  fill: none;
  stroke: rgba(255, 255, 255, 0.1);
  stroke-width: 2;
}

.score-fill {
  fill: none;
  stroke: #667eea;
  stroke-width: 2;
  stroke-linecap: round;
  transition: stroke-dasharray 0.3s ease;
}

.score-fill.energy {
  stroke: #f093fb;
}

.score-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.score-value {
  font-size: 1.2rem;
  font-weight: 700;
  line-height: 1;
}

.score-label {
  font-size: 0.7rem;
  opacity: 0.8;
  margin-top: 0.25rem;
}

.current-mood {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.mood-emoji {
  font-size: 3rem;
}

.mood-info {
  flex: 1;
}

.mood-name {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.mood-description {
  font-size: 0.9rem;
  opacity: 0.7;
}

.weather-factors {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.factor-title {
  font-size: 1rem;
  font-weight: 600;
  opacity: 0.9;
}

.factors-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.factor-item {
  display: grid;
  grid-template-columns: auto 1fr auto;
  grid-template-rows: auto auto;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.factor-icon {
  grid-row: 1 / -1;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
}

.factor-label {
  font-size: 0.8rem;
  opacity: 0.8;
  font-weight: 500;
}

.factor-value {
  font-size: 0.9rem;
  font-weight: 600;
  text-align: right;
}

.factor-impact {
  grid-column: 2 / -1;
  font-size: 0.7rem;
  font-weight: 500;
  text-align: right;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  justify-self: end;
}

.factor-impact.positive {
  background: rgba(34, 197, 94, 0.2);
  color: #86efac;
}

.factor-impact.negative {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
}

.factor-impact.neutral {
  background: rgba(156, 163, 175, 0.2);
  color: #d1d5db;
}

.recommendation {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
  border-radius: 15px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.recommendation-icon {
  font-size: 1.5rem;
  margin-top: 0.25rem;
}

.recommendation-text {
  font-size: 1rem;
  line-height: 1.5;
  font-weight: 500;
}

.no-data {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem 0;
  text-align: center;
}

@media (max-width: 768px) {
  .mood-scores {
    gap: 1rem;
  }
  
  .score-circle {
    width: 60px;
    height: 60px;
  }
  
  .score-value {
    font-size: 1rem;
  }
  
  .score-label {
    font-size: 0.6rem;
  }
  
  .current-mood {
    flex-direction: column;
    text-align: center;
  }
  
  .factors-grid {
    grid-template-columns: 1fr;
  }
  
  .factor-item {
    grid-template-columns: auto 1fr;
    grid-template-rows: auto auto auto;
  }
  
  .factor-value {
    text-align: left;
  }
  
  .factor-impact {
    grid-column: 1 / -1;
    justify-self: start;
  }
}
</style>
