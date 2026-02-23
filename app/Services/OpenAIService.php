<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    /**
     * Analyze an image using OpenAI GPT-4 Vision.
     *
     * @param string $imageUrl
     * @return array|null JSON decoded response with keys: keywords, color, brand, category, distinct_features
     */
    public function analyzeImage(string $imageUrl): ?array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            Log::warning('OpenAI API key not configured.');
            return null;
        }

        // If local URL (localhost/127.0.0.1), we cannot send it to OpenAI.
        // We must assume the image is accessible via public URL or send base64.
        // For this implementation, we will try to fetch the file content and send as base64.
        
        $base64Image = $this->getImageAsBase64($imageUrl);
        
        if (!$base64Image) {
            Log::error("Could not convert image to base64: $imageUrl");
            return null;
        }

        $prompt = "Analyze this image of a lost/found item. " .
                  "Return a STRICT JSON object (no markdown, no extra text) with the following keys: " .
                  "keywords (array of strings, e.g. ['backpack', 'blue', 'zipper']), " .
                  "color (string), " .
                  "brand (string or null), " .
                  "category (string, suggest a broad category), " .
                  "distinct_features (string, description of scratches, stickers, or unique marks).";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:image/jpeg;base64,{$base64Image}"
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 300,
                'response_format' => [ 'type' => 'json_object' ]
            ]);

            if ($response->failed()) {
                Log::error('OpenAI API Error: ' . $response->body());
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if ($content) {
                return json_decode($content, true);
            }

        } catch (\Exception $e) {
            Log::error('OpenAI Analysis Exception: ' . $e->getMessage());
        }

        return null;
    }

    private function getImageAsBase64(string $url): ?string
    {
        // Check if it's a local storage path
        // URL format: http://127.0.0.1:8000/storage/reports/filename.jpg
        // Local path: storage/app/public/reports/filename.jpg
        
        try {
            // Attempt to resolve local path from URL if hosted locally
            $path = parse_url($url, PHP_URL_PATH); // /storage/reports/xyz.jpg
            
            // Remove /storage prefix to get relative path in storage/app/public
            $relativePath = preg_replace('/^\/?storage\//', '', $path);
            $localPath = storage_path("app/public/{$relativePath}");

            if (file_exists($localPath)) {
                $imageData = file_get_contents($localPath);
                return base64_encode($imageData);
            }

            // Fallback: try to fetch via HTTP (works for external URLs)
            $response = Http::get($url);
            if ($response->successful()) {
                return base64_encode($response->body());
            }

        } catch (\Exception $e) {
            Log::error("Image fetch failed: " . $e->getMessage());
        }

        return null;
    }
}
