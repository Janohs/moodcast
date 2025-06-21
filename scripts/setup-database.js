#!/usr/bin/env node

/**
 * Database Setup Script for MoodCast
 * 
 * This script helps set up the Supabase database tables.
 * 
 * Instructions:
 * 1. Go to your Supabase dashboard: https://supabase.com/dashboard
 * 2. Navigate to your project: https://supabase.com/dashboard/project/jibunkdteetnauphsuqj
 * 3. Go to the SQL Editor
 * 4. Copy and paste the contents of database/schema.sql
 * 5. Run the script
 * 
 * Or you can use the Supabase CLI:
 * 1. Install Supabase CLI: npm install -g supabase
 * 2. supabase login
 * 3. supabase db push --linked --schema database/schema.sql
 */

const fs = require('fs');
const path = require('path');

console.log('📊 MoodCast Database Setup');
console.log('=========================\n');

const schemaPath = path.join(__dirname, '..', 'database', 'schema.sql');

if (fs.existsSync(schemaPath)) {
  console.log('✅ Schema file found at:', schemaPath);
  console.log('\n📋 To set up your database:');
  console.log('1. Go to https://supabase.com/dashboard/project/jibunkdteetnauphsuqj/sql');
  console.log('2. Copy the contents of database/schema.sql');
  console.log('3. Paste and run in the SQL editor');
  console.log('\n📁 Schema file contents preview:');
  console.log('=====================================');
  
  const schemaContent = fs.readFileSync(schemaPath, 'utf8');
  console.log(schemaContent.substring(0, 500) + '...\n');
  
  console.log('✨ After running the schema:');
  console.log('- Start the frontend: npm run dev:frontend');
  console.log('- Navigate to http://localhost:5173/database-test');
  console.log('- Test the database connection');
  
} else {
  console.log('❌ Schema file not found at:', schemaPath);
  console.log('Please ensure you are running this from the project root directory.');
}

console.log('\n🔑 Environment variables needed:');
console.log('VITE_SUPABASE_URL=https://jibunkdteetnauphsuqj.supabase.co');
console.log('VITE_SUPABASE_KEY=your_anon_key');
console.log('\n(These are already set in frontend/.env)');
