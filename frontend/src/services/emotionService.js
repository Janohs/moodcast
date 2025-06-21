class EmotionService {
  constructor() {
    this.apiBaseUrl = 'http://localhost:8000';
  }

  /**
   * Create a new emotion entry
   */
  async createEmotion(emotionData) {
    try {
      console.log('EmotionService: Creating emotion with data:', emotionData);
      console.log('EmotionService: API URL:', `${this.apiBaseUrl}/api/emotions`);
      
      const response = await fetch(`${this.apiBaseUrl}/api/emotions`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(emotionData)
      });

      console.log('EmotionService: Response status:', response.status);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      // Normalize backend response
      if (result.status === 'success') {
        return { success: true, ...result };
      } else if (result.status === 'error') {
        return { success: false, error: result.message || 'Unknown error', ...result };
      }
      return result;
    } catch (error) {
      console.error('EmotionService: Error creating emotion:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Get user's emotion entries
   */
  async getUserEmotions(userId, limit = 30, days = 30) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/api/emotions?user_id=${userId}&limit=${limit}&days=${days}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        }
      });

      const result = await response.json();
      if (result.status === 'success') {
        return { success: true, ...result };
      } else if (result.status === 'error') {
        return { success: false, error: result.message || 'Unknown error', ...result };
      }
      return result;
    } catch (error) {
      console.error('Error fetching emotions:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Get emotion insights and analytics
   */
  async getEmotionInsights(userId, days = 30) {
    try {
      console.log('EmotionService: Getting insights for user:', userId, 'days:', days);
      const url = `${this.apiBaseUrl}/api/emotions/insights?user_id=${userId}&days=${days}`;
      console.log('EmotionService: API URL:', url);
      
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        }
      });

      console.log('EmotionService: Response status:', response.status);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      if (result.status === 'success') {
        return { success: true, ...result };
      } else if (result.status === 'error') {
        return { success: false, error: result.message || 'Unknown error', ...result };
      }
      return result;
    } catch (error) {
      console.error('EmotionService: Error fetching insights:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Delete an emotion entry
   */
  async deleteEmotion(emotionId) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/api/emotions/${emotionId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      return result;
    } catch (error) {
      console.error('Error deleting emotion:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Get emotion options for the mood entry form
   */
  getEmotionOptions() {
    return [
      { value: 'happy', label: 'Happy', emoji: '😊', color: 'green' },
      { value: 'excited', label: 'Excited', emoji: '🤗', color: 'yellow' },
      { value: 'calm', label: 'Calm', emoji: '😌', color: 'blue' },
      { value: 'relaxed', label: 'Relaxed', emoji: '😎', color: 'teal' },
      { value: 'content', label: 'Content', emoji: '😇', color: 'emerald' },
      { value: 'neutral', label: 'Neutral', emoji: '😐', color: 'gray' },
      { value: 'tired', label: 'Tired', emoji: '😴', color: 'indigo' },
      { value: 'stressed', label: 'Stressed', emoji: '😰', color: 'orange' },
      { value: 'anxious', label: 'Anxious', emoji: '😟', color: 'amber' },
      { value: 'sad', label: 'Sad', emoji: '😢', color: 'blue' },
      { value: 'frustrated', label: 'Frustrated', emoji: '😤', color: 'red' },
      { value: 'angry', label: 'Angry', emoji: '😠', color: 'red' }
    ];
  }

  /**
   * Get intensity levels
   */
  getIntensityLevels() {
    return [
      { value: 1, label: 'Very Low', description: 'Barely noticeable' },
      { value: 2, label: 'Low', description: 'Mild feeling' },
      { value: 3, label: 'Moderate', description: 'Noticeable but manageable' },
      { value: 4, label: 'High', description: 'Strong and clear' },
      { value: 5, label: 'Very High', description: 'Overwhelming' }
    ];
  }
}

// Export singleton instance
export const emotionService = new EmotionService();
export default emotionService;
