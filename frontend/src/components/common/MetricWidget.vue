<template>
  <div class="metric-widget">
    <div class="metric-value">{{ value }}</div>
    <div class="metric-label">{{ label }}</div>
    <div v-if="subtitle" class="metric-subtitle">{{ subtitle }}</div>
    <div v-if="showIndicator" class="metric-indicator">
      <div class="indicator-circle">
        <svg class="indicator-svg" viewBox="0 0 36 36">
          <path
            class="indicator-bg"
            d="M18 2.0845
              a 15.9155 15.9155 0 0 1 0 31.831
              a 15.9155 15.9155 0 0 1 0 -31.831"
          />
          <path
            class="indicator-fill"
            :stroke-dasharray="`${percentage}, 100`"
            d="M18 2.0845
              a 15.9155 15.9155 0 0 1 0 31.831
              a 15.9155 15.9155 0 0 1 0 -31.831"
          />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  value: {
    type: [String, Number],
    required: true
  },
  label: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  showIndicator: {
    type: Boolean,
    default: false
  },
  percentage: {
    type: Number,
    default: 0
  }
})
</script>

<style scoped>
.metric-widget {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  position: relative;
}

.metric-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.metric-label {
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
  opacity: 0.9;
}

.metric-subtitle {
  font-size: 0.75rem;
  opacity: 0.7;
  margin-bottom: 1rem;
}

.metric-indicator {
  margin-top: 0.5rem;
}

.indicator-circle {
  width: 60px;
  height: 60px;
}

.indicator-svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.indicator-bg {
  fill: none;
  stroke: rgba(255, 255, 255, 0.2);
  stroke-width: 2;
}

.indicator-fill {
  fill: none;
  stroke: #667eea;
  stroke-width: 2;
  stroke-linecap: round;
  transition: stroke-dasharray 0.3s ease;
}
</style>
