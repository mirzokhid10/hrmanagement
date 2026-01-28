<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    protected ?string $apiKey;

    public function __construct()
    {
        // BEST PRACTICE: Always use config(), never env() in classes.
        // Ensure you add 'gemini' => ['api_key' => env('GEMINI_API_KEY')] in config/services.php
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Standard Text Request (Good for Job Descriptions, Chatbots)
     */
    public function ask(string $prompt, float $temperature = 0.7): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API Key is missing.');
            return null;
        }

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => 1000, // Increased for long job descriptions
                    ]
                ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::error('Gemini API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Gemini Connection Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * NEW: Structured Data Request (Crucial for Resume Screening)
     * This forces the AI to return an array we can save to the DB.
     */
    public function askJson(string $prompt): array
    {
        // 1. Force the AI to be a JSON machine
        $jsonPrompt = $prompt . "\n\nIMPORTANT: Return ONLY valid JSON. No markdown formatting (no ```json).";

        // 2. Get the text
        $result = $this->ask($jsonPrompt, 0.2); // Low temperature for consistent data

        if (!$result) {
            return [];
        }

        // 3. Clean the Markdown (Gemini often ignores the "no markdown" rule)
        $cleanJson = str_replace(['```json', '```', "\n"], '', $result);

        // 4. Decode
        $data = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gemini JSON Decode Error: ' . json_last_error_msg() . ' | Raw: ' . $result);
            return [];
        }

        return $data;
    }
}
