<template>
  <div class="mood-forecast">
    <div class="forecast-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab"
        :class="{ active: activeTab === tab }"
        @click="activeTab = tab"
        class="tab"
      >
        {{ tab }}
      </button>
    </div>
    
    <div class="forecast-timeline">
      <div 
        v-for="item in forecastData" 
        :key="item.time"
        :class="{ active: item.active }"
        class="time-slot"
      >
        <div class="time">{{ item.time }}</div>
        <div class="mood-icon">{{ item.icon }}</div>
        <div class="mood-temp">{{ item.mood }}</div>
        <div class="mood-label">{{ item.label }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('Hourly Forecast')
const tabs = ['Hourly Forecast', 'Weekly Forecast']

const forecastData = ref([
  { time: '12 AM', icon: '🎵', mood: 'Happy', label: '85%', active: false },
  { time: 'Now', icon: '🎶', mood: 'Energetic', label: '92%', active: true },
  { time: '2 AM', icon: '🎸', mood: 'Calm', label: '78%', active: false },
  { time: '3 AM', icon: '🎹', mood: 'Focus', label: '88%', active: false },
  { time: '4 AM', icon: '🎺', mood: 'Chill', label: '75%', active: false }
])
</script>

<style scoped>
.mood-forecast {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 1.5rem;
  color: white;
}

.forecast-tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.tab {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  font-size: 1rem;
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

.forecast-timeline {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  padding: 0.5rem 0;
}

.time-slot {
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  padding: 1rem 0.75rem;
  text-align: center;
  min-width: 80px;
  transition: all 0.3s ease;
}

.time-slot.active {
  background: rgba(139, 69, 195, 0.6);
  transform: scale(1.05);
}

.time {
  font-size: 0.85rem;
  opacity: 0.8;
  margin-bottom: 0.5rem;
}

.mood-icon {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.mood-temp {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.mood-label {
  font-size: 0.75rem;
  opacity: 0.8;
}
</style>
