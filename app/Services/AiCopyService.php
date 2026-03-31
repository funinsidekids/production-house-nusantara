<?php

namespace App\Services;

use OpenAI;

class AiCopyService
{
    public function generateSlideCaption(string $title, string $context): string
    {
        $apiKey = config('services.openai.key');
        if (! $apiKey) {
            return "Cinematic storytelling untuk {$title} dengan sentuhan Nusantara modern.";
        }

        $client = OpenAI::client($apiKey);
        $response = $client->chat()->create([
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => 'You write premium cinematic captions in Indonesian.'],
                ['role' => 'user', 'content' => "Buat satu caption maksimal 22 kata untuk slider production house. Judul: {$title}. Konteks: {$context}"],
            ],
            'temperature' => 0.9,
            'max_tokens' => 60,
        ]);

        return trim($response->choices[0]->message->content ?? '') ?: "Cinematic storytelling untuk {$title} dengan sentuhan Nusantara modern.";
    }
}
