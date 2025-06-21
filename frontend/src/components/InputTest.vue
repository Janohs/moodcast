<template>
  <div class="p-4 bg-blue-500 text-white min-h-screen">
    <h2 class="text-xl font-bold mb-4">Connection Test</h2>
    
    <!-- Backend Connection Test -->
    <div class="mb-6 p-4 bg-white/20 rounded">
      <h3 class="font-semibold mb-2">Backend Connection Test</h3>
      <button 
        @click="testBackend" 
        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 mb-2"
        :disabled="testing"
      >
        {{ testing ? 'Testing...' : 'Test Backend Connection' }}
      </button>
      <div v-if="backendResult" class="mt-2 p-2 bg-black/20 rounded text-sm">
        <strong>Result:</strong> {{ backendResult }}
      </div>
      <div v-if="backendError" class="mt-2 p-2 bg-red-500/20 rounded text-sm">
        <strong>Error:</strong> {{ backendError }}
      </div>
    </div>

    <!-- Input Test -->
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-2">Test Input 1:</label>
        <input 
          v-model="testValue1" 
          type="text" 
          class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded text-white placeholder-white/60"
          placeholder="Type something..."
        />
        <p class="mt-1 text-sm">Value: {{ testValue1 }}</p>
      </div>
      
      <!-- Form Test -->
      <div>
        <label class="block text-sm font-medium mb-2">Auth Test:</label>
        <form @submit.prevent="handleAuth" class="space-y-2">
          <input 
            v-model="formData.name" 
            type="text" 
            class="w-full px-3 py-2 bg-white text-black rounded"
            placeholder="Your name..."
          />
          <input 
            v-model="formData.email" 
            type="email" 
            class="w-full px-3 py-2 bg-white text-black rounded"
            placeholder="Your email..."
          />
          <input 
            v-model="formData.password" 
            type="password" 
            class="w-full px-3 py-2 bg-white text-black rounded"
            placeholder="Password..."
          />
          <button 
            type="submit" 
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            :disabled="authTesting"
          >
            {{ authTesting ? 'Testing Auth...' : 'Test Registration' }}
          </button>
        </form>
        <div class="mt-2 p-2 bg-black/20 rounded">
          <p class="text-xs">Form Data: {{ JSON.stringify(formData) }}</p>
        </div>
        <div v-if="authResult" class="mt-2 p-2 bg-green-500/20 rounded text-sm">
          <strong>Auth Success:</strong> {{ authResult }}
        </div>
        <div v-if="authError" class="mt-2 p-2 bg-red-500/20 rounded text-sm">
          <strong>Auth Error:</strong> {{ authError }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const testValue1 = ref('')
const testing = ref(false)
const authTesting = ref(false)
const backendResult = ref('')
const backendError = ref('')
const authResult = ref('')
const authError = ref('')

const formData = reactive({
  name: '',
  email: '',
  password: ''
})

async function testBackend() {
  testing.value = true
  backendResult.value = ''
  backendError.value = ''
  
  try {
    console.log('Testing backend connection to http://localhost:8001/health')
    
    const response = await fetch('http://localhost:8001/health', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
    
    console.log('Response status:', response.status)
    console.log('Response headers:', [...response.headers.entries()])
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }
    
    const data = await response.json()
    backendResult.value = JSON.stringify(data, null, 2)
    console.log('Backend response:', data)
    
  } catch (error) {
    console.error('Backend test error:', error)
    backendError.value = `${error.name}: ${error.message}`
  } finally {
    testing.value = false
  }
}

async function handleAuth() {
  authTesting.value = true
  authResult.value = ''
  authError.value = ''
  
  try {
    console.log('Testing auth with data:', formData)
    
    const response = await fetch('http://localhost:8001/api/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: formData.name,
        email: formData.email,
        password: formData.password
      })
    })
    
    console.log('Auth response status:', response.status)
    
    if (!response.ok) {
      const errorText = await response.text()
      throw new Error(`HTTP ${response.status}: ${errorText}`)
    }
    
    const data = await response.json()
    authResult.value = `User created: ${data.data?.user?.name} (${data.data?.user?.email})`
    console.log('Auth response:', data)
    
  } catch (error) {
    console.error('Auth test error:', error)
    authError.value = `${error.name}: ${error.message}`
  } finally {
    authTesting.value = false
  }
}
</script>
