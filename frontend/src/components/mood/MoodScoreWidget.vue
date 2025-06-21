<template>
  <div class="mood-score-widget">
    <div class="score-value">{{ score }}</div>
    <div class="score-label">{{ label }}</div>
    <div class="score-progress">
      <div class="progress-track">
        <div class="progress-fill" :style="{ width: `${percentage}%` }"></div>
      </div>
    </div>
    <div class="score-subtitle">{{ subtitle }}</div>
  </div>
</template>

<script setup>
defineProps({
  score: {
    type: [String, Number],
    required: true
  },
  label: {
    type: String,
    required: true
  },
  percentage: {
    type: Number,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  }
})
</script>

<style scoped>
.mood-score-widget {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.score-value {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.score-label {
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 1rem;
  opacity: 0.9;
}

.score-progress {
  width: 100%;
  margin-bottom: 0.5rem;
}

.progress-track {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  border-radius: 2px;
  transition: width 0.3s ease;
}

.score-subtitle {
  font-size: 0.75rem;
  opacity: 0.7;
}
</style>
