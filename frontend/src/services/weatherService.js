import { weatherService as dbWeatherService } from '../utils/database.js';

class WeatherAPIService {
  constructor() {
    // We'll call the backend PHP API that we already have
    this.apiBaseUrl = 'http://localhost:8001';
  }

  /**
   * Get current weather with database caching
   * @param {string} location - Location to get weather for
   * @param {boolean} forceRefresh - Force API call even if cached data exists
   */
  async getCurrentWeather(location = 'auto:ip', forceRefresh = false) {
    try {
      // First, check if we have recent cached data (unless forcing refresh)
      if (!forceRefresh) {
        const { hasRecent, error: cacheError } = await dbWeatherService.hasRecentWeatherData(location);
        
        if (!cacheError && hasRecent) {
          console.log('📦 Using cached weather data for:', location);
          const { data: cachedData, error: fetchError } = await dbWeatherService.getRecentWeatherLog(location);
          
          if (!fetchError && cachedData) {
            return {
              success: true,
              data: this.formatWeatherData(cachedData),
              source: 'cache'
            };
          }
        }
      }

      console.log('🌐 Fetching fresh weather data from API for:', location);
      
      // Fetch fresh data from our backend API
      const response = await fetch(`${this.apiBaseUrl}/api/weather/current?location=${encodeURIComponent(location)}`);
      
      if (!response.ok) {
        throw new Error(`Weather API error: ${response.status}`);
      }
      
      const apiResult = await response.json();
      
      if (apiResult.status !== 'success') {
        throw new Error(apiResult.message || 'Failed to fetch weather data');
      }

      // Store the fresh data in our database
      const weatherLogData = this.prepareWeatherForDatabase(apiResult.data, location);
      const { data: savedData, error: saveError } = await dbWeatherService.saveWeatherLog(weatherLogData);
      
      if (saveError) {
        console.warn('⚠️ Failed to save weather data to database:', saveError.message);
        // Continue anyway, just return the API data without caching
      } else {
        console.log('💾 Weather data saved to database');
      }

      return {
        success: true,
        data: apiResult.data,
        source: 'api',
        cached: !saveError
      };

    } catch (error) {
      console.error('❌ Weather service error:', error);
      
      // If API fails, try to return any cached data we have, even if old
      try {
        const { data: fallbackData, error: fallbackError } = await dbWeatherService.getRecentWeatherLog(location);
        
        if (!fallbackError && fallbackData) {
          console.log('🔄 Using fallback cached data');
          return {
            success: true,
            data: this.formatWeatherData(fallbackData),
            source: 'fallback',
            warning: 'Using cached data due to API error'
          };
        }
      } catch (fallbackError) {
        console.error('❌ Fallback also failed:', fallbackError);
      }

      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Get weather forecast (this will primarily use API as forecasts change frequently)
   */
  async getForecast(location = 'auto:ip', days = 3) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/api/weather/forecast?location=${encodeURIComponent(location)}&days=${days}`);
      
      if (!response.ok) {
        throw new Error(`Forecast API error: ${response.status}`);
      }
      
      const result = await response.json();
      return result;

    } catch (error) {
      console.error('❌ Forecast service error:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Search for locations
   */
  async searchLocations(query) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/api/weather/search?q=${encodeURIComponent(query)}`);
      
      if (!response.ok) {
        throw new Error(`Search API error: ${response.status}`);
      }
      
      const result = await response.json();
      return result;

    } catch (error) {
      console.error('❌ Location search error:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Prepare weather data for database storage
   */
  prepareWeatherForDatabase(apiData, requestedLocation) {
    const { location, current } = apiData;
    
    return {
      location: `${location.name}, ${location.region}, ${location.country}`,
      latitude: location.lat,
      longitude: location.lon,
      temperature: current.temp_c,
      humidity: current.humidity,
      pressure: current.pressure_mb,
      weather_condition: current.condition.text,
      weather_description: `${current.condition.text} - Feels like ${current.feelslike_c}°C`,
      wind_speed: current.wind_kph,
      wind_direction: this.getWindDirection(current.wind_dir),
      visibility: current.vis_km || null,
      uv_index: current.uv,
      recorded_at: new Date(location.localtime).toISOString()
    };
  }

  /**
   * Format cached database data to match API format
   */
  formatWeatherData(dbData) {
    return {
      location: {
        name: dbData.location.split(',')[0]?.trim() || dbData.location,
        region: dbData.location.split(',')[1]?.trim() || '',
        country: dbData.location.split(',')[2]?.trim() || '',
        lat: dbData.latitude,
        lon: dbData.longitude,
        localtime: dbData.recorded_at
      },
      current: {
        temp_c: dbData.temperature,
        temp_f: (dbData.temperature * 9/5) + 32,
        condition: {
          text: dbData.weather_condition,
          icon: this.getWeatherIcon(dbData.weather_condition),
          code: this.getWeatherCode(dbData.weather_condition)
        },
        wind_kph: dbData.wind_speed,
        wind_mph: dbData.wind_speed * 0.621371,
        wind_dir: this.getWindDirectionFromDegrees(dbData.wind_direction),
        pressure_mb: dbData.pressure,
        humidity: dbData.humidity,
        feelslike_c: dbData.temperature, // Approximate
        feelslike_f: (dbData.temperature * 9/5) + 32,
        uv: dbData.uv_index,
        vis_km: dbData.visibility
      }
    };
  }

  /**
   * Convert wind direction to degrees (helper)
   */
  getWindDirection(direction) {
    const directions = {
      'N': 0, 'NNE': 22.5, 'NE': 45, 'ENE': 67.5,
      'E': 90, 'ESE': 112.5, 'SE': 135, 'SSE': 157.5,
      'S': 180, 'SSW': 202.5, 'SW': 225, 'WSW': 247.5,
      'W': 270, 'WNW': 292.5, 'NW': 315, 'NNW': 337.5
    };
    return directions[direction] || 0;
  }

  /**
   * Convert degrees to wind direction (helper)
   */
  getWindDirectionFromDegrees(degrees) {
    const directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                       'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
    return directions[Math.round(degrees / 22.5) % 16];
  }

  /**
   * Get weather icon based on condition (helper)
   */
  getWeatherIcon(condition) {
    // Simple mapping - you can enhance this
    const iconMap = {
      'Sunny': '//cdn.weatherapi.com/weather/64x64/day/113.png',
      'Clear': '//cdn.weatherapi.com/weather/64x64/night/113.png',
      'Cloudy': '//cdn.weatherapi.com/weather/64x64/day/119.png',
      'Rainy': '//cdn.weatherapi.com/weather/64x64/day/296.png',
    };
    return iconMap[condition] || '//cdn.weatherapi.com/weather/64x64/day/113.png';
  }

  /**
   * Get weather code based on condition (helper)
   */
  getWeatherCode(condition) {
    // Simple mapping - you can enhance this
    const codeMap = {
      'Sunny': 1000,
      'Clear': 1000,
      'Cloudy': 1006,
      'Rainy': 1180,
    };
    return codeMap[condition] || 1000;
  }

  /**
   * Get weather history for a location
   */
  async getWeatherHistory(location, limit = 10) {
    try {
      const { data, error } = await dbWeatherService.getWeatherLogs(location, limit);
      
      if (error) {
        throw new Error(error.message);
      }

      return {
        success: true,
        data: data || [],
        count: data?.length || 0
      };

    } catch (error) {
      console.error('❌ Weather history error:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }
}

// Export singleton instance
export const weatherAPIService = new WeatherAPIService();
export default weatherAPIService;
