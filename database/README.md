# MoodCast Database Setup

This directory contains the database schema and setup scripts for the MoodCast application using Supabase.

## Quick Setup

### 1. Database Schema Setup

1. Go to your [Supabase Dashboard](https://supabase.com/dashboard/project/jibunkdteetnauphsuqj/sql)
2. Copy the contents of `schema.sql`
3. Paste into the SQL Editor and run

### 2. Environment Configuration

The frontend environment is already configured in `frontend/.env`:
```
VITE_SUPABASE_URL=https://jibunkdteetnauphsuqj.supabase.co
VITE_SUPABASE_KEY=your_anon_key
```

### 3. Test the Connection

1. Start the development server:
   ```bash
   cd frontend && npm run dev
   ```

2. Navigate to [http://localhost:5173/database-test](http://localhost:5173/database-test)

3. Test the database operations:
   - Sign up / Sign in
   - Add weather logs
   - Add emotion entries

## Database Schema

### Tables Overview

#### `users`
- User profiles and authentication
- Weather preferences stored as JSON
- Row Level Security (RLS) enabled

#### `weather_logs`
- Cached weather data to reduce API calls
- Includes location, temperature, conditions, etc.
- Public read access, authenticated insert

#### `emotions`
- User emotion entries linked to weather
- Tracks emotion type, intensity, and weather preference
- User can only access their own emotions

### Key Features

- **Row Level Security**: Users can only access their own data
- **Weather Caching**: Reduces external API calls
- **Emotion Tracking**: Links emotions to specific weather conditions
- **User Preferences**: Stores preferred weather conditions

## Database Services

### Authentication (`authService`)
- Sign up/in/out
- Session management
- Auth state changes

### User Service (`userService`)
- Profile management
- Weather preferences
- User settings

### Weather Service (`weatherService`)
- Weather data caching
- Location-based queries
- Recent data checks

### Emotion Service (`emotionService`)
- Emotion CRUD operations
- Analytics queries
- Weather correlation

## Usage Example

```javascript
import { authService, emotionService, weatherService } from '@/utils/database.js'

// Sign in user
const { data, error } = await authService.signIn(email, password)

// Save emotion with weather
const emotion = await emotionService.saveEmotion({
  emotion_type: 'happy',
  intensity: 8,
  notes: 'Beautiful sunny day!',
  weather_liked: true,
  weather_log_id: weatherLogId
})

// Get user's emotion history
const emotions = await emotionService.getUserEmotions(30)
```

## Security

- All tables use Row Level Security (RLS)
- Users can only access their own emotions
- Weather logs are public (cached data)
- Authentication required for personal data

## Analytics Potential

The schema supports analytics queries like:
- Most common emotions for weather types
- User weather preferences vs actual emotions
- Seasonal mood patterns
- Temperature vs mood correlations

## Development

To modify the schema:
1. Update `schema.sql`
2. Run the updated script in Supabase SQL Editor
3. Update TypeScript types if needed
4. Test with the database test component
