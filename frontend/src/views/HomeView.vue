<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/authService.js'

const router = useRouter()

onMounted(() => {
  // Redirect based on authentication status
  if (authService.isAuthenticated()) {
    router.push('/dashboard')
  } else {
    router.push('/auth')
  }
})
</script>

<template>
  <div class="home flex items-center justify-center min-h-screen">
    <div class="text-center">
      <div class="text-6xl mb-4">🌦️</div>
      <h1 class="text-4xl font-bold text-white mb-2">MoodCast</h1>
      <p class="text-white/80">Loading...</p>
    </div>
  </div>
</template>

<style scoped>
.home {
  min-height: 100vh;
  color: white;
  width: 100%;
}
</style>
