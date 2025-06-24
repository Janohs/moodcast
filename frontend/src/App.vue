<script setup>
import { RouterView } from 'vue-router'
</script>

<template>

  <div class="app-container">
    <!-- Background wallpaper -->
    <div class="wallpaper-bg"></div>
    
    <!-- Content overlay -->
    <div class="content-overlay">
      <RouterView />
    </div>
  </div>
</template>

<style scoped>
.app-container {
  position: relative;
  min-height: 100vh;
  overflow: hidden;
}

.wallpaper-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: url('@/assets/wallpaper.png');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: -1;
}

.content-overlay {
  position: relative;
  min-height: 100vh;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(1px);
}
</style>