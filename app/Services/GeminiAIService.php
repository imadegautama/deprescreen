<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAIService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash-preview-05-20');

        if (!$this->apiKey) {
            throw new \Exception('GEMINI_API_KEY is not configured');
        }
    }

    /**
     * Generate text response using Gemini API
     *
     * @param string $prompt The prompt to send to Gemini
     * @param int $maxTokens Maximum tokens in response
     * @return string Generated text response
     * @throws \Exception If API call fails
     */
    public function generateText(string $prompt, int $maxTokens = 500): string
    {
        try {
            // Build URL dengan API key
            $url = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(30)
                ->post(
                    $url,
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $prompt,
                                    ],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'maxOutputTokens' => $maxTokens,
                            'temperature' => 0.7,
                        ],
                    ]
                );

            if (!$response->successful()) {
                $statusCode = $response->status();
                $body = $response->body();

                // Log detailed error
                Log::error('Gemini API Error:', [
                    'status' => $statusCode,
                    'body' => $body,
                    'prompt_length' => strlen($prompt),
                    'model' => $this->model,
                ]);

                // Handle specific error codes
                if ($statusCode === 403) {
                    throw new \Exception(
                        'Gemini API Permission Denied (403). API key may be invalid or API not enabled.'
                    );
                }

                if ($statusCode === 400) {
                    throw new \Exception(
                        "Gemini API Bad Request (400): {$body}"
                    );
                }

                throw new \Exception(
                    "Gemini API Error: {$statusCode} - {$body}"
                );
            }

            $data = $response->json();

            // Extract text from response
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            }

            throw new \Exception('Unexpected Gemini API response structure');
        } catch (\Exception $e) {
            Log::error('Gemini Service Error:', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt),
            ]);

            throw $e;
        }
    }

    /**
     * Validate API key by making a test request
     *
     * @return bool True if API key is valid
     */
    public function validateApiKey(): bool
    {
        try {
            $response = Http::timeout(10)
                ->post(
                    "{$this->baseUrl}/{$this->model}:generateContent",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => 'Hello',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'x-goog-api-key' => $this->apiKey,
                    ]
                );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Gemini API Key Validation Failed:', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
