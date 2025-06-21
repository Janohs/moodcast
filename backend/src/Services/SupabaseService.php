<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SupabaseService
{
    private $client;
    private $apiUrl;
    private $apiKey;
    private $serviceKey;

    public function __construct()
    {
        $this->apiUrl = $_ENV['SUPABASE_URL'] ?? 'https://your-project.supabase.co';
        $this->apiKey = $_ENV['SUPABASE_ANON_KEY'] ?? '';
        $this->serviceKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? '';
        
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => 30,
        ]);
    }

    /**
     * Create a new user in Supabase
     */
    public function createUser($userData)
    {
        try {
            $response = $this->client->post('/rest/v1/users', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                    'Prefer' => 'return=representation'
                ],
                'json' => [
                    'email' => $userData['email'],
                    'name' => $userData['name'],
                    'password_hash' => $userData['password_hash'],
                    'preferred_weather' => json_encode($userData['preferred_weather'] ?? [])
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data[0] ?? null;

        } catch (RequestException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : '';
            throw new \Exception('Failed to create user in Supabase: ' . $e->getMessage() . ' - ' . $responseBody);
        }
    }

    /**
     * Find user by email in Supabase
     */
    public function findUserByEmail($email)
    {
        try {
            $response = $this->client->get('/rest/v1/users', [
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ],
                'query' => [
                    'email' => 'eq.' . $email,
                    'select' => '*'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (!empty($data)) {
                $user = $data[0];
                // Parse the preferred_weather JSON (check if it's already an array)
                $preferredWeather = $user['preferred_weather'] ?? '[]';
                if (is_string($preferredWeather)) {
                    $user['weather_preferences'] = json_decode($preferredWeather, true);
                } else {
                    $user['weather_preferences'] = $preferredWeather;
                }
                return $user;
            }
            
            return null;

        } catch (RequestException $e) {
            throw new \Exception('Failed to find user in Supabase: ' . $e->getMessage());
        }
    }

    /**
     * Find user by ID in Supabase
     */
    public function findUserById($userId)
    {
        try {
            $response = $this->client->get('/rest/v1/users', [
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ],
                'query' => [
                    'id' => 'eq.' . $userId,
                    'select' => '*'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (!empty($data)) {
                $user = $data[0];
                // Parse the preferred_weather JSON (check if it's already an array) and remove password_hash from response
                $preferredWeather = $user['preferred_weather'] ?? '[]';
                if (is_string($preferredWeather)) {
                    $user['weather_preferences'] = json_decode($preferredWeather, true);
                } else {
                    $user['weather_preferences'] = $preferredWeather;
                }
                unset($user['password_hash']);
                return $user;
            }
            
            return null;

        } catch (RequestException $e) {
            throw new \Exception('Failed to find user by ID in Supabase: ' . $e->getMessage());
        }
    }

    /**
     * Update user preferences in Supabase
     */
    public function updateUserPreferences($userId, $preferences)
    {
        try {
            $response = $this->client->patch('/rest/v1/users', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ],
                'query' => [
                    'id' => 'eq.' . $userId
                ],
                'json' => [
                    'preferred_weather' => json_encode($preferences),
                    'updated_at' => date('c') // ISO 8601 format
                ]
            ]);

            return true;

        } catch (RequestException $e) {
            throw new \Exception('Failed to update user preferences in Supabase: ' . $e->getMessage());
        }
    }

    /**
     * Check if user exists by email
     */
    public function userExists($email)
    {
        $user = $this->findUserByEmail($email);
        return $user !== null;
    }

    /**
     * Create a new emotion entry
     */
    public function createEmotion($emotionData)
    {
        try {
            $response = $this->client->post('/rest/v1/emotions', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                    'Prefer' => 'return=representation'
                ],
                'json' => [
                    'user_id' => $emotionData['user_id'],
                    'weather_log_id' => $emotionData['weather_log_id'] ?? null,
                    'emotion_type' => $emotionData['emotion_type'],
                    'intensity' => $emotionData['intensity'],
                    'notes' => $emotionData['notes'] ?? null,
                    'weather_liked' => $emotionData['weather_liked'] ?? null
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return [
                'success' => true,
                'data' => $data[0] ?? null
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'Failed to create emotion in Supabase: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user's emotions
     */
    public function getUserEmotions($userId, $limit = 30, $days = 30)
    {
        try {
            $dateFrom = date('c', strtotime("-{$days} days")); // ISO 8601 format
            
            $response = $this->client->get('/rest/v1/emotions', [
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ],
                'query' => [
                    'user_id' => 'eq.' . $userId,
                    'created_at' => 'gte.' . $dateFrom,
                    'order' => 'created_at.desc',
                    'limit' => $limit
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return [
                'success' => true,
                'data' => $data
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'Failed to get emotions from Supabase: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get emotion insights/analytics
     */
    public function getEmotionInsights($userId, $days = 30)
    {
        try {
            $dateFrom = date('c', strtotime("-{$days} days")); // ISO 8601 format
            
            // Get emotions with weather data
            $response = $this->client->get('/rest/v1/emotions', [
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ],
                'query' => [
                    'user_id' => 'eq.' . $userId,
                    'created_at' => 'gte.' . $dateFrom,
                    'select' => 'emotion_type,intensity,weather_liked,created_at,weather_logs(temperature,weather_condition)'
                ]
            ]);

            $emotions = json_decode($response->getBody()->getContents(), true);
            
            // Calculate insights
            $insights = $this->calculateInsights($emotions);
            
            return [
                'success' => true,
                'data' => $insights
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'Failed to get insights from Supabase: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete an emotion entry
     */
    public function deleteEmotion($emotionId)
    {
        try {
            $response = $this->client->delete("/rest/v1/emotions?id=eq.{$emotionId}", [
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ]
            ]);

            return [
                'success' => true
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'Failed to delete emotion from Supabase: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate emotion insights from raw data
     */
    private function calculateInsights($emotions)
    {
        if (empty($emotions)) {
            return [
                'total_entries' => 0,
                'average_mood' => 0,
                'mood_trends' => [],
                'weather_correlations' => [],
                'emotion_distribution' => []
            ];
        }

        $totalEntries = count($emotions);
        $intensitySum = array_sum(array_column($emotions, 'intensity'));
        $averageMood = round($intensitySum / $totalEntries, 1);

        // Emotion distribution
        $emotionCounts = array_count_values(array_column($emotions, 'emotion_type'));
        
        // Weather correlations
        $weatherLiked = array_filter($emotions, fn($e) => $e['weather_liked'] === true);
        $weatherDisliked = array_filter($emotions, fn($e) => $e['weather_liked'] === false);
        
        // Mood trends (group by day)
        $moodTrends = [];
        foreach ($emotions as $emotion) {
            $date = date('Y-m-d', strtotime($emotion['created_at']));
            if (!isset($moodTrends[$date])) {
                $moodTrends[$date] = [];
            }
            $moodTrends[$date][] = $emotion['intensity'];
        }
        
        // Calculate daily averages
        foreach ($moodTrends as $date => $intensities) {
            $moodTrends[$date] = round(array_sum($intensities) / count($intensities), 1);
        }

        return [
            'total_entries' => $totalEntries,
            'average_mood' => $averageMood,
            'mood_trends' => $moodTrends,
            'weather_correlations' => [
                'liked_weather_count' => count($weatherLiked),
                'disliked_weather_count' => count($weatherDisliked),
                'neutral_weather_count' => $totalEntries - count($weatherLiked) - count($weatherDisliked)
            ],
            'emotion_distribution' => $emotionCounts
        ];
    }
}
