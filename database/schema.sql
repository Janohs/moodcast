-- Supabase Database Schema for MoodCast Application

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    preferred_weather JSONB DEFAULT '{}', -- Store weather preferences as JSON
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create weather_logs table
CREATE TABLE IF NOT EXISTS weather_logs (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    temperature DECIMAL(5, 2),
    humidity INTEGER,
    pressure DECIMAL(7, 2),
    weather_condition VARCHAR(100),
    weather_description TEXT,
    wind_speed DECIMAL(5, 2),
    wind_direction INTEGER,
    visibility DECIMAL(5, 2),
    uv_index DECIMAL(3, 1),
    recorded_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create emotions table
CREATE TABLE IF NOT EXISTS emotions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    weather_log_id UUID REFERENCES weather_logs(id) ON DELETE SET NULL,
    emotion_type VARCHAR(50) NOT NULL, -- happy, sad, anxious, calm, energetic, etc.
    intensity INTEGER CHECK (intensity >= 1 AND intensity <= 10), -- 1-10 scale
    notes TEXT,
    weather_liked BOOLEAN DEFAULT NULL, -- true if user liked the weather, false if not, null if neutral
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_weather_logs_location ON weather_logs(location);
CREATE INDEX IF NOT EXISTS idx_weather_logs_recorded_at ON weather_logs(recorded_at);
CREATE INDEX IF NOT EXISTS idx_emotions_user_id ON emotions(user_id);
CREATE INDEX IF NOT EXISTS idx_emotions_weather_log_id ON emotions(weather_log_id);
CREATE INDEX IF NOT EXISTS idx_emotions_created_at ON emotions(created_at);

-- Enable Row Level Security (RLS)
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE weather_logs ENABLE ROW LEVEL SECURITY;
ALTER TABLE emotions ENABLE ROW LEVEL SECURITY;

-- RLS Policies for users table
CREATE POLICY "Users can view own profile" ON users
    FOR SELECT USING (auth.uid() = id);

CREATE POLICY "Users can update own profile" ON users
    FOR UPDATE USING (auth.uid() = id);

CREATE POLICY "Users can insert own profile" ON users
    FOR INSERT WITH CHECK (auth.uid() = id);

-- RLS Policies for weather_logs table
CREATE POLICY "Weather logs are viewable by everyone" ON weather_logs
    FOR SELECT USING (true);

CREATE POLICY "Authenticated users can insert weather logs" ON weather_logs
    FOR INSERT TO authenticated WITH CHECK (true);

-- RLS Policies for emotions table
CREATE POLICY "Users can view own emotions" ON emotions
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own emotions" ON emotions
    FOR INSERT WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update own emotions" ON emotions
    FOR UPDATE USING (auth.uid() = user_id);

CREATE POLICY "Users can delete own emotions" ON emotions
    FOR DELETE USING (auth.uid() = user_id);

-- Function to automatically update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Trigger for users table
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
