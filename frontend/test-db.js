// Simple database connection test
import { supabase } from './src/utils/supabase.js';

async function testDatabaseConnection() {
  console.log('🔄 Testing Supabase connection...');
  
  try {
    // Test 1: Check if we can connect to Supabase
    console.log('1. Testing basic connection...');
    const { data, error } = await supabase
      .from('weather_logs')
      .select('count(*)')
      .limit(1);
    
    if (error) {
      console.error('❌ Connection failed:', error.message);
      return false;
    }
    
    console.log('✅ Basic connection works!');
    
    // Test 2: Try to insert a test weather log
    console.log('2. Testing weather_logs insert...');
    const weatherTest = await supabase
      .from('weather_logs')
      .insert({
        location: 'Test City',
        temperature: 25.5,
        weather_condition: 'Clear',
        weather_description: 'Database test entry',
        recorded_at: new Date().toISOString()
      })
      .select();
    
    if (weatherTest.error) {
      console.error('❌ Weather insert failed:', weatherTest.error.message);
      return false;
    }
    
    console.log('✅ Weather logs table works!', weatherTest.data);
    
    // Test 3: Try to read the data back
    console.log('3. Testing data retrieval...');
    const readTest = await supabase
      .from('weather_logs')
      .select('*')
      .eq('location', 'Test City')
      .limit(1);
    
    if (readTest.error) {
      console.error('❌ Data retrieval failed:', readTest.error.message);
      return false;
    }
    
    console.log('✅ Data retrieval works!', readTest.data);
    
    console.log('🎉 All database tests passed!');
    return true;
    
  } catch (err) {
    console.error('❌ Unexpected error:', err);
    return false;
  }
}

// Run the test
testDatabaseConnection();
