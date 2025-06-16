# MoodCast - Weather-Based Mood Tracking App

## Setup Instructions

### Prerequisites
- Node.js (v18+)
- PHP (v8.0+)
- Composer
- Supabase account

### Installation
```bash
# Install all dependencies
npm run install:all

# Set up environment variables
# 1. Copy .env files in frontend/ and backend/
# 2. Update with your Supabase credentials
# 3. Add your weather API key

# Start development servers
npm run dev
```

### Access
- Frontend: http://localhost:5173
- Backend API: http://localhost:8000

## Project Structure
- `frontend/` - Vue.js application
- `backend/` - PHP Slim API
- `config/` - Configuration files
- `docs/` - Documentation

## Environment Variables
See .env.example files in frontend/ and backend/ directories.
