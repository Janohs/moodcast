class EmotionService {
  constructor() {
    this.apiBaseUrl = 'http://localhost:8001';
  }

  /**
   * Create a new emotion entry
   */
  async createEmotion(emotionData) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/api/emotions`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(emotionData)
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      return result;
    } catch (error) {
      console.error('Error creating emotion:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Get user's emotion entries
   */
  async getUserEmotions(limit = 30, days = 30) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/api/emotions?limit=${limit}&days=${days}`, {
        method: 'GET',
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
  async getEmotionInsights(days = 30) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/api/emotions/insights?days=${days}`, {
        method: 'GET',
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
      console.error('Error fetching insights:', error);
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
