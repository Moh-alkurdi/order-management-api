<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiClassificationService
{
    public function classifyMessage(string $orderNumber, string $customerMessage): string
    {
        $prompt = "You are an AI Support Classifier. Analyze the following customer message for Order {$orderNumber}. 
        If the customer is complaining about:
        1. Shipping delay of more than 10 days.
        2. Payment issues or double charge.
        Then you MUST start your response with the word [CRITICAL] followed by a short explanation.
        Otherwise, if it's a normal question, just write a friendly response to the customer.
        Customer Message: '{$customerMessage}'";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'groq/compound-mini',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.2
        ]);

        return $response->json('choices.0.message.content', 'Failed to get response from AI.');
    }
}