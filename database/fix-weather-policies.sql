-- Fix Row Level Security for weather_logs table

-- Drop existing policies
DROP POLICY IF EXISTS "Weather logs are viewable by everyone" ON weather_logs;
DROP POLICY IF EXISTS "Authenticated users can insert weather logs" ON weather_logs;

-- Create new, more permissive policies for weather_logs
-- Weather logs should be public data that anyone can read and insert

-- Allow everyone to view weather logs
CREATE POLICY "Allow public read access to weather_logs" ON weather_logs
    FOR SELECT USING (true);

-- Allow everyone to insert weather logs (since this is public weather data)
CREATE POLICY "Allow public insert access to weather_logs" ON weather_logs
    FOR INSERT WITH CHECK (true);

-- Allow authenticated users to update weather logs
CREATE POLICY "Allow authenticated update to weather_logs" ON weather_logs
    FOR UPDATE TO authenticated WITH CHECK (true);

-- Allow authenticated users to delete weather logs
CREATE POLICY "Allow authenticated delete to weather_logs" ON weather_logs
    FOR DELETE TO authenticated USING (true);
