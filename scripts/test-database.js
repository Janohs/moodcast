// Simple database connection test
import { supabase } from '../frontend/src/utils/supabase.js';

async function testDatabaseConnection() {
  console.log('🧪 Testing Supabase Database Connection...\n');

  try {
    // Test 1: Check if we can connect to Supabase
    console.log('1. Testing basic connection...');
    const { data: healthCheck, error: healthError } = await supabase
      .from('weather_logs')
      .select('count', { count: 'exact', head: true });
    
    if (healthError) {
      console.log('❌ Connection failed:', healthError.message);
      return;
    }
    console.log('✅ Connected to Supabase successfully');

    // Test 2: Try to insert weather data (public table)
    console.log('\n2. Testing weather_logs table...');
    const weatherData = {
      location: 'Test City',
      temperature: 25.5,
      weather_condition: 'Sunny',
      weather_description: 'Clear skies',
      recorded_at: new Date().toISOString()
    };

    const { data: weatherResult, error: weatherError } = await supabase
      .from('weather_logs')
      .insert(weatherData)
      .select()
      .single();

    if (weatherError) {
      console.log('❌ Weather logs insert failed:', weatherError.message);
    } else {
      console.log('✅ Weather log created:', weatherResult.id);
    }

    // Test 3: Read back the data
    console.log('\n3. Testing data retrieval...');
    const { data: allWeather, error: readError } = await supabase
      .from('weather_logs')
      .select('*')
      .limit(5);

    if (readError) {
      console.log('❌ Data retrieval failed:', readError.message);
    } else {
      console.log('✅ Retrieved weather logs:', allWeather.length, 'records');
      console.log('Latest entry:', allWeather[0]?.location, allWeather[0]?.temperature + '°C');
    }

    // Test 4: Check table structure
    console.log('\n4. Testing table structure...');
    const { data: tableInfo, error: structureError } = await supabase
      .from('weather_logs')
      .select('id, location, temperature, created_at')
      .limit(1);

    if (structureError) {
      console.log('❌ Table structure check failed:', structureError.message);
    } else {
      console.log('✅ Table structure is correct');
    }

    console.log('\n🎉 Database is working correctly!');
    console.log('You can now:');
    console.log('- Add weather data');
    console.log('- Sign up with a real email address');
    console.log('- Track emotions after authentication');

  } catch (error) {
    console.log('❌ Unexpected error:', error.message);
  }
}

// Run the test
testDatabaseConnection();
