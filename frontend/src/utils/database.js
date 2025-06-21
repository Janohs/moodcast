import { supabase } from './supabase.js';

// User operations
export const userService = {
  // Get current user profile
  async getProfile() {
    const { data: { user } } = await supabase.auth.getUser();
    if (!user) return null;

    const { data, error } = await supabase
      .from('users')
      .select('*')
      .eq('id', user.id)
      .single();

    return { data, error };
  },

  // Create or update user profile
  async upsertProfile(profile) {
    const { data: { user } } = await supabase.auth.getUser();
    if (!user) throw new Error('No authenticated user');

    const { data, error } = await supabase
      .from('users')
      .upsert({
        id: user.id,
        email: user.email,
        ...profile
      })
      .select()
      .single();

    return { data, error };
  },

  // Update weather preferences
  async updateWeatherPreferences(preferences) {
    const { data: { user } } = await supabase.auth.getUser();
    if (!user) throw new Error('No authenticated user');

    const { data, error } = await supabase
      .from('users')
      .update({ preferred_weather: preferences })
      .eq('id', user.id)
      .select()
      .single();

    return { data, error };
  }
};

// Weather logs operations
export const weatherService = {
  // Get weather logs by location
  async getWeatherLogs(location, limit = 10) {
    const { data, error } = await supabase
      .from('weather_logs')
      .select('*')
      .eq('location', location)
      .order('recorded_at', { ascending: false })
      .limit(limit);

    return { data, error };
  },

  // Get recent weather log for location
  async getRecentWeatherLog(location) {
    const { data, error } = await supabase
      .from('weather_logs')
      .select('*')
      .eq('location', location)
      .order('recorded_at', { ascending: false })
      .limit(1)
      .single();

    return { data, error };
  },

  // Save weather data
  async saveWeatherLog(weatherData) {
    const { data, error } = await supabase
      .from('weather_logs')
      .insert(weatherData)
      .select()
      .single();

    return { data, error };
  },

  // Check if we have recent weather data (within last hour)
  async hasRecentWeatherData(location) {
    const oneHourAgo = new Date(Date.now() - 60 * 60 * 1000).toISOString();
    
    const { data, error } = await supabase
      .from('weather_logs')
      .select('id')
      .eq('location', location)
      .gte('recorded_at', oneHourAgo)
      .limit(1);

    return { hasRecent: data && data.length > 0, error };
  }
};

// Emotions operations
export const emotionService = {
  // Get user's emotions
  async getUserEmotions(limit = 50) {
    const { data, error } = await supabase
      .from('emotions')
      .select(`
        *,
        weather_logs (
          temperature,
          weather_condition,
          weather_description,
          location
        )
      `)
      .order('created_at', { ascending: false })
      .limit(limit);

    return { data, error };
  },

  // Save emotion entry
  async saveEmotion(emotionData) {
    const { data: { user } } = await supabase.auth.getUser();
    if (!user) throw new Error('No authenticated user');

    const { data, error } = await supabase
      .from('emotions')
      .insert({
        user_id: user.id,
        ...emotionData
      })
      .select(`
        *,
        weather_logs (
          temperature,
          weather_condition,
          weather_description,
          location
        )
      `)
      .single();

    return { data, error };
  },

  // Update emotion entry
  async updateEmotion(emotionId, updates) {
    const { data, error } = await supabase
      .from('emotions')
      .update(updates)
      .eq('id', emotionId)
      .select(`
        *,
        weather_logs (
          temperature,
          weather_condition,
          weather_description,
          location
        )
      `)
      .single();

    return { data, error };
  },

  // Delete emotion entry
  async deleteEmotion(emotionId) {
    const { data, error } = await supabase
      .from('emotions')
      .delete()
      .eq('id', emotionId);

    return { data, error };
  },

  // Get emotion analytics
  async getEmotionAnalytics() {
    const { data, error } = await supabase
      .from('emotions')
      .select(`
        emotion_type,
        intensity,
        weather_liked,
        weather_logs (
          weather_condition,
          temperature
        )
      `);

    return { data, error };
  }
};

// Authentication helpers
export const authService = {
  // Sign up
  async signUp(email, password) {
    const { data, error } = await supabase.auth.signUp({
      email,
      password
    });

    return { data, error };
  },

  // Sign in
  async signIn(email, password) {
    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password
    });

    return { data, error };
  },

  // Sign out
  async signOut() {
    const { error } = await supabase.auth.signOut();
    return { error };
  },

  // Get current session
  async getSession() {
    const { data, error } = await supabase.auth.getSession();
    return { data, error };
  },

  // Listen to auth changes
  onAuthStateChange(callback) {
    return supabase.auth.onAuthStateChange(callback);
  }
};
