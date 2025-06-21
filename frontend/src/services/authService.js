class AuthService {
  constructor() {
    this.apiBaseUrl = 'http://localhost:8000/api/auth';
    this.tokenKey = 'moodcast_token';
    this.refreshTokenKey = 'moodcast_refresh_token';
    this.userKey = 'moodcast_user';
  }

  /**
   * Register a new user
   */
  async register(userData) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
      });

      const result = await response.json();

      if (result.status === 'success') {
        // Store tokens and user data
        this.setTokens(result.data.access_token, result.data.refresh_token);
        this.setUser(result.data.user);
        return { success: true, data: result.data };
      } else {
        return { success: false, error: result.message, errors: result.errors };
      }
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Login user
   */
  async login(email, password) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password })
      });

      const result = await response.json();

      if (result.status === 'success') {
        // Store tokens and user data
        this.setTokens(result.data.access_token, result.data.refresh_token);
        this.setUser(result.data.user);
        this.setWeatherPreferences(result.data.weather_preferences);
        return { success: true, data: result.data };
      } else {
        return { success: false, error: result.message };
      }
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Logout user
   */
  logout() {
    localStorage.removeItem(this.tokenKey);
    localStorage.removeItem(this.refreshTokenKey);
    localStorage.removeItem(this.userKey);
    localStorage.removeItem('moodcast_weather_preferences');
  }

  /**
   * Get current user profile
   */
  async getCurrentUser() {
    try {
      const response = await this.authenticatedRequest('/me');
      
      if (response.success) {
        this.setUser(response.data.user);
        this.setWeatherPreferences(response.data.weather_preferences);
        return response;
      }
      
      return response;
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Update weather preferences
   */
  async updateWeatherPreferences(preferences) {
    try {
      const response = await this.authenticatedRequest('/preferences', {
        method: 'PUT',
        body: JSON.stringify({ preferences })
      });

      if (response.success) {
        this.setWeatherPreferences(preferences);
      }

      return response;
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Make authenticated request
   */
  async authenticatedRequest(endpoint, options = {}) {
    const token = this.getToken();
    
    if (!token) {
      throw new Error('No authentication token found');
    }

    const defaultOptions = {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    };

    const requestOptions = {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...(options.headers || {})
      }
    };

    const response = await fetch(`${this.apiBaseUrl}${endpoint}`, requestOptions);
    const result = await response.json();

    if (response.status === 401) {
      // Token might be expired, try to refresh or logout
      this.logout();
      throw new Error('Authentication expired, please login again');
    }

    return result;
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return !!this.getToken() && !!this.getUser();
  }

  /**
   * Get stored token
   */
  getToken() {
    return localStorage.getItem(this.tokenKey);
  }

  /**
   * Get stored user
   */
  getUser() {
    const userStr = localStorage.getItem(this.userKey);
    return userStr ? JSON.parse(userStr) : null;
  }

  /**
   * Get weather preferences
   */
  getWeatherPreferences() {
    const prefsStr = localStorage.getItem('moodcast_weather_preferences');
    return prefsStr ? JSON.parse(prefsStr) : {};
  }

  /**
   * Store tokens
   */
  setTokens(accessToken, refreshToken) {
    localStorage.setItem(this.tokenKey, accessToken);
    if (refreshToken) {
      localStorage.setItem(this.refreshTokenKey, refreshToken);
    }
  }

  /**
   * Store user data
   */
  setUser(user) {
    localStorage.setItem(this.userKey, JSON.stringify(user));
  }

  /**
   * Store weather preferences
   */
  setWeatherPreferences(preferences) {
    localStorage.setItem('moodcast_weather_preferences', JSON.stringify(preferences));
  }

  /**
   * Get authorization header for other API calls
   */
  getAuthHeader() {
    const token = this.getToken();
    return token ? { 'Authorization': `Bearer ${token}` } : {};
  }
}

// Export singleton instance
export const authService = new AuthService();
export default authService;
