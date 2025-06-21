import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useWeatherStore = defineStore('weather', () => {
  // State
  const currentWeather = ref(null)
  const forecast = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const selectedLocation = ref(null)

  // API base URL
  const API_BASE_URL = 'http://localhost:8000/api/weather'

  // Getters
  const isLoading = computed(() => loading.value)
  const hasError = computed(() => !!error.value)
  const currentTemp = computed(() => currentWeather.value?.current?.temp_c || 0)
  const currentCondition = computed(() => currentWeather.value?.current?.condition?.text || 'Unknown')
  const currentIcon = computed(() => currentWeather.value?.current?.condition?.icon || '')
  const locationName = computed(() => currentWeather.value?.location?.name || 'Unknown Location')
  
  // Get today's forecast
  const todayForecast = computed(() => {
    if (!forecast.value?.forecast?.[0]) return null
    return forecast.value.forecast[0]
  })

  // Get hourly forecast for today
  const hourlyForecast = computed(() => {
    if (!todayForecast.value?.hour) return []
    
    const now = new Date()
    const currentHour = now.getHours()
    
    // Return next 24 hours starting from current hour
    return todayForecast.value.hour.filter((hour) => {
      const hourTime = new Date(hour.time)
      return hourTime.getHours() >= currentHour
    }).slice(0, 24)
  })

  // Get daily forecast
  const dailyForecast = computed(() => {
    if (!forecast.value?.forecast) return []
    return forecast.value.forecast.map(day => ({
      date: day.date,
      maxTemp: day.day.maxtemp_c,
      minTemp: day.day.mintemp_c,
      condition: day.day.condition.text,
      icon: day.day.condition.icon,
      chanceOfRain: day.day.daily_chance_of_rain,
      humidity: day.day.avghumidity,
      uv: day.day.uv
    }))
  })

  // Actions
  async function fetchCurrentWeather(location = null) {
    loading.value = true
    error.value = null
    
    try {
      const url = new URL(`${API_BASE_URL}/current`)
      if (location) {
        url.searchParams.append('location', location)
      }
      
      const response = await fetch(url)
      const data = await response.json()
      
      if (data.status === 'success') {
        currentWeather.value = data.data
        selectedLocation.value = location
      } else {
        throw new Error(data.message || 'Failed to fetch weather data')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching current weather:', err)
    } finally {
      loading.value = false
    }
  }

  async function fetchForecast(location = null, days = 3) {
    loading.value = true
    error.value = null
    
    try {
      const url = new URL(`${API_BASE_URL}/forecast`)
      if (location) {
        url.searchParams.append('location', location)
      }
      url.searchParams.append('days', days.toString())
      
      const response = await fetch(url)
      const data = await response.json()
      
      if (data.status === 'success') {
        forecast.value = data.data
        currentWeather.value = {
          location: data.data.location,
          current: data.data.current
        }
        selectedLocation.value = location
      } else {
        throw new Error(data.message || 'Failed to fetch forecast data')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching forecast:', err)
    } finally {
      loading.value = false
    }
  }

  async function searchLocations(query) {
    if (!query || query.length < 3) return []
    
    try {
      const url = new URL(`${API_BASE_URL}/search`)
      url.searchParams.append('q', query)
      
      const response = await fetch(url)
      const data = await response.json()
      
      if (data.status === 'success') {
        return data.data
      } else {
        throw new Error(data.message || 'Failed to search locations')
      }
    } catch (err) {
      console.error('Error searching locations:', err)
      return []
    }
  }

  // Initialize with user's location
  async function initialize() {
    await fetchForecast()
  }

  // Clear error
  function clearError() {
    error.value = null
  }

  // Get weather-based mood suggestion
  const moodSuggestion = computed(() => {
    if (!currentWeather.value) return null
    
    const condition = currentWeather.value.current.condition.text.toLowerCase()
    const temp = currentWeather.value.current.temp_c
    const humidity = currentWeather.value.current.humidity
    const uv = currentWeather.value.current.uv
    
    // Simple mood calculation based on weather
    let mood = 'neutral'
    let energy = 50
    let happiness = 50
    
    // Temperature effects
    if (temp >= 20 && temp <= 25) {
      happiness += 20
      energy += 10
    } else if (temp < 10 || temp > 30) {
      happiness -= 10
      energy -= 15
    }
    
    // Condition effects
    if (condition.includes('sunny') || condition.includes('clear')) {
      mood = 'happy'
      happiness += 25
      energy += 20
    } else if (condition.includes('cloudy') || condition.includes('overcast')) {
      happiness -= 5
      energy -= 10
    } else if (condition.includes('rain') || condition.includes('drizzle')) {
      mood = 'calm'
      happiness -= 15
      energy -= 20
    } else if (condition.includes('storm') || condition.includes('thunder')) {
      mood = 'energetic'
      energy += 15
      happiness -= 10
    } else if (condition.includes('snow')) {
      mood = 'peaceful'
      happiness += 5
      energy -= 5
    }
    
    // Humidity effects
    if (humidity > 80) {
      energy -= 10
    } else if (humidity < 30) {
      energy -= 5
    }
    
    // UV effects
    if (uv > 6) {
      energy += 10
    }
    
    // Normalize values
    happiness = Math.max(0, Math.min(100, happiness))
    energy = Math.max(0, Math.min(100, energy))
    
    return {
      mood,
      happiness: Math.round(happiness),
      energy: Math.round(energy),
      recommendation: getMoodRecommendation(mood)
    }
  })

  function getMoodRecommendation(mood) {
    const recommendations = {
      happy: [
        'Perfect weather for outdoor activities!',
        'Great day to spend time with friends',
        'Ideal conditions for a walk or exercise'
      ],
      calm: [
        'Perfect weather for reading or meditation',
        'Good time for indoor creative activities',
        'Cozy weather for relaxation'
      ],
      energetic: [
        'Great weather for high-energy activities',
        'Perfect for indoor workouts',
        'Good time to tackle challenging projects'
      ],
      peaceful: [
        'Beautiful weather for quiet contemplation',
        'Perfect for indoor hobbies',
        'Great time to enjoy warm beverages'
      ],
      neutral: [
        'Moderate weather for various activities',
        'Good balance for indoor and outdoor plans',
        'Flexible conditions for any mood'
      ]
    }
    
    const moodRecs = recommendations[mood] || recommendations.neutral
    return moodRecs[Math.floor(Math.random() * moodRecs.length)]
  }

  return {
    // State
    currentWeather,
    forecast,
    loading,
    error,
    selectedLocation,
    
    // Getters
    isLoading,
    hasError,
    currentTemp,
    currentCondition,
    currentIcon,
    locationName,
    todayForecast,
    hourlyForecast,
    dailyForecast,
    moodSuggestion,
    
    // Actions
    fetchCurrentWeather,
    fetchForecast,
    searchLocations,
    initialize,
    clearError
  }
})
