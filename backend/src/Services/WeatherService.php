<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WeatherService
{
    private $client;
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = $_ENV['WEATHER_API_KEY'] ?? '39b6055822724607984105814252106';
        $this->baseUrl = 'https://api.weatherapi.com/v1';
    }

    /**
     * Get current weather for a location
     */
    public function getCurrentWeather($location = 'auto:ip')
    {
        try {
            $response = $this->client->get($this->baseUrl . '/current.json', [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $location,
                    'aqi' => 'yes'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => true,
                'data' => [
                    'location' => [
                        'name' => $data['location']['name'],
                        'region' => $data['location']['region'],
                        'country' => $data['location']['country'],
                        'lat' => $data['location']['lat'],
                        'lon' => $data['location']['lon'],
                        'localtime' => $data['location']['localtime']
                    ],
                    'current' => [
                        'temp_c' => $data['current']['temp_c'],
                        'temp_f' => $data['current']['temp_f'],
                        'condition' => [
                            'text' => $data['current']['condition']['text'],
                            'icon' => $data['current']['condition']['icon'],
                            'code' => $data['current']['condition']['code']
                        ],
                        'wind_mph' => $data['current']['wind_mph'],
                        'wind_kph' => $data['current']['wind_kph'],
                        'wind_dir' => $data['current']['wind_dir'],
                        'pressure_mb' => $data['current']['pressure_mb'],
                        'humidity' => $data['current']['humidity'],
                        'cloud' => $data['current']['cloud'],
                        'feelslike_c' => $data['current']['feelslike_c'],
                        'feelslike_f' => $data['current']['feelslike_f'],
                        'uv' => $data['current']['uv']
                    ],
                    'air_quality' => $data['current']['air_quality'] ?? null
                ]
            ];
        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch weather data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get weather forecast for a location
     */
    public function getForecast($location = 'auto:ip', $days = 3)
    {
        try {
            $response = $this->client->get($this->baseUrl . '/forecast.json', [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $location,
                    'days' => min($days, 10), // Max 10 days
                    'aqi' => 'yes',
                    'alerts' => 'yes'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => true,
                'data' => [
                    'location' => [
                        'name' => $data['location']['name'],
                        'region' => $data['location']['region'],
                        'country' => $data['location']['country'],
                        'lat' => $data['location']['lat'],
                        'lon' => $data['location']['lon'],
                        'localtime' => $data['location']['localtime']
                    ],
                    'current' => [
                        'temp_c' => $data['current']['temp_c'],
                        'temp_f' => $data['current']['temp_f'],
                        'condition' => [
                            'text' => $data['current']['condition']['text'],
                            'icon' => $data['current']['condition']['icon'],
                            'code' => $data['current']['condition']['code']
                        ],
                        'humidity' => $data['current']['humidity'],
                        'uv' => $data['current']['uv']
                    ],
                    'forecast' => array_map(function($day) {
                        return [
                            'date' => $day['date'],
                            'day' => [
                                'maxtemp_c' => $day['day']['maxtemp_c'],
                                'maxtemp_f' => $day['day']['maxtemp_f'],
                                'mintemp_c' => $day['day']['mintemp_c'],
                                'mintemp_f' => $day['day']['mintemp_f'],
                                'avgtemp_c' => $day['day']['avgtemp_c'],
                                'avgtemp_f' => $day['day']['avgtemp_f'],
                                'condition' => [
                                    'text' => $day['day']['condition']['text'],
                                    'icon' => $day['day']['condition']['icon'],
                                    'code' => $day['day']['condition']['code']
                                ],
                                'maxwind_mph' => $day['day']['maxwind_mph'],
                                'maxwind_kph' => $day['day']['maxwind_kph'],
                                'avghumidity' => $day['day']['avghumidity'],
                                'daily_chance_of_rain' => $day['day']['daily_chance_of_rain'],
                                'daily_chance_of_snow' => $day['day']['daily_chance_of_snow'],
                                'uv' => $day['day']['uv']
                            ],
                            'hour' => array_map(function($hour) {
                                return [
                                    'time' => $hour['time'],
                                    'temp_c' => $hour['temp_c'],
                                    'temp_f' => $hour['temp_f'],
                                    'condition' => [
                                        'text' => $hour['condition']['text'],
                                        'icon' => $hour['condition']['icon'],
                                        'code' => $hour['condition']['code']
                                    ],
                                    'wind_mph' => $hour['wind_mph'],
                                    'wind_kph' => $hour['wind_kph'],
                                    'humidity' => $hour['humidity'],
                                    'cloud' => $hour['cloud'],
                                    'feelslike_c' => $hour['feelslike_c'],
                                    'feelslike_f' => $hour['feelslike_f'],
                                    'chance_of_rain' => $hour['chance_of_rain'],
                                    'chance_of_snow' => $hour['chance_of_snow'],
                                    'uv' => $hour['uv']
                                ];
                            }, $day['hour'])
                        ];
                    }, $data['forecast']['forecastday']),
                    'alerts' => $data['alerts']['alert'] ?? []
                ]
            ];
        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch forecast data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Search for locations
     */
    public function searchLocations($query)
    {
        try {
            $response = $this->client->get($this->baseUrl . '/search.json', [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $query
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => true,
                'data' => array_map(function($location) {
                    return [
                        'id' => $location['id'],
                        'name' => $location['name'],
                        'region' => $location['region'],
                        'country' => $location['country'],
                        'lat' => $location['lat'],
                        'lon' => $location['lon'],
                        'url' => $location['url']
                    ];
                }, $data)
            ];
        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'error' => 'Failed to search locations: ' . $e->getMessage()
            ];
        }
    }
}
