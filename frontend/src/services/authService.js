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
      console.log('AuthService: Attempting registration with data:', userData);
      console.log('AuthService: API URL:', `${this.apiBaseUrl}/register`);
      
      const response = await fetch(`${this.apiBaseUrl}/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
      });

      console.log('AuthService: Response status:', response.status);
      console.log('AuthService: Response headers:', [...response.headers.entries()]);

      const result = await response.json();
      console.log('AuthService: Response data:', result);

      if (result.status === 'success') {
        // Store user data (no tokens needed for simple auth)
        this.setUser(result.data.user);
        return { success: true, data: result.data };
      } else {
        return { success: false, error: result.message, errors: result.errors };
      }
    } catch (error) {
      console.error('AuthService: Registration error:', error);
      console.error('Error details:', {
        name: error.name,
        message: error.message,
        stack: error.stack
      });
      return { success: false, error: `Connection failed: ${error.message}` };
    }
  }

  /**
   * Login user
   */
  async login(email, password) {
    try {
      console.log('AuthService: Attempting login with email:', email);
      console.log('AuthService: API URL:', `${this.apiBaseUrl}/login`);
      
      const response = await fetch(`${this.apiBaseUrl}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password })
      });

      console.log('AuthService: Login response status:', response.status);

      const result = await response.json();
      console.log('AuthService: Login response data:', result);

      if (result.status === 'success') {
        // Store user data (no tokens needed for simple auth)
        this.setUser(result.data.user);
        this.setWeatherPreferences(result.data.user.weather_preferences || []);
        
        // Store password temporarily for session (for authenticated requests)
        // Note: This is not ideal for security, but needed for simple auth
        sessionStorage.setItem('moodcast_session_password', password);
        
        return { success: true, data: result.data };
      } else {
        return { success: false, error: result.message };
      }
    } catch (error) {
      console.error('AuthService: Login error:', error);
      return { success: false, error: error.message };
    }
  }

  /**
   * Logout user
   */
  logout() {
    localStorage.removeItem(this.userKey);
    localStorage.removeItem('moodcast_weather_preferences');
    sessionStorage.removeItem('moodcast_session_password');
  }

  /**
   * Get current user profile
   */
  async getCurrentUser() {
    // For simple auth, we need email and password stored temporarily
    const user = this.getUser();
    if (!user || !user.email) {
      return { success: false, error: 'No user data found' };
    }

    // For now, just return the stored user data
    // In a real implementation, you might want to verify credentials
    return { 
      success: true, 
      data: { 
        user: user,
        weather_preferences: this.getWeatherPreferences()
      }
    };
  }

  /**
   * Update weather preferences
   */
  async updateWeatherPreferences(preferences) {
    try {
      const user = this.getUser();
      if (!user || !user.email) {
        return { success: false, error: 'No user authentication found' };
      }

      // For simple auth, we need to store user's password temporarily during session
      // This is not ideal for security, but for demo purposes
      const password = sessionStorage.getItem('moodcast_session_password');
      if (!password) {
        return { success: false, error: 'Session expired. Please login again.' };
      }

      const response = await fetch(`${this.apiBaseUrl}/preferences`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
          email: user.email,
          password: password,
          preferences: preferences 
        })
      });

      const result = await response.json();

      if (result.status === 'success') {
        this.setWeatherPreferences(preferences);
        return { success: true, data: result };
      } else {
        return { success: false, error: result.message };
      }
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Make authenticated request (using stored credentials for simple auth)
   */
  async authenticatedRequest(endpoint, options = {}) {
    const user = this.getUser();
    const sessionPassword = sessionStorage.getItem('moodcast_session_password');
    
    if (!user || !sessionPassword) {
      throw new Error('No authentication credentials found. Please login again.');
    }

    const defaultOptions = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: user.email,
        password: sessionPassword,
        ...(options.body ? JSON.parse(options.body) : {})
      })
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
      // Authentication failed, logout
      this.logout();
      throw new Error('Authentication expired, please login again');
    }

    return result;
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return !!this.getUser();
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
