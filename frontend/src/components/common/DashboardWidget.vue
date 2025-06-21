<template>
  <div class="widget" :class="widgetClass">
    <div class="widget-header">
      <span class="widget-icon">{{ icon }}</span>
      <span class="widget-title">{{ title }}</span>
    </div>
    <div class="widget-content">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  widgetClass: {
    type: String,
    default: ''
  }
})
</script>

<style scoped>
.widget {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 1.5rem;
  color: white;
  transition: all 0.3s ease;
}

.widget:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}

.widget-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.widget-icon {
  font-size: 1.2rem;
}

.widget-title {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  opacity: 0.8;
}

.widget-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
</style>
