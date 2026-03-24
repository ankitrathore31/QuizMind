<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AIQuizService — Wraps Groq API calls for quiz generation.
 * Uses llama-3.3-70b-versatile for text, meta-llama/llama-4-scout for vision.
 *
 * config/services.php must have:
 *   'groq' => [
 *       'api_key'      => env('GROQ_API_KEY'),
 *       'model'        => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
 *       'vision_model' => env('GROQ_VISION_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
 *   ],
 *
 * .env:
 *   GROQ_API_KEY=gsk_xxxx
 */
class AIQuizService
{
    private string $apiKey;
    private string $model;
    private string $visionModel;
    private string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey      = config('services.groq.api_key');
        $this->model       = config('services.groq.model', 'llama-3.3-70b-versatile');
        $this->visionModel = config('services.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct');

        if (empty($this->apiKey)) {
            throw new \RuntimeException('GROQ_API_KEY is not set. Add it to .env and run: php artisan config:clear');
        }
    }

    // ── Build standard MCQ prompt ───────────────────────────────────────────
    private function buildPrompt(string $context, int $count, string $difficulty): string
    {
        return <<<PROMPT
You are an expert educational quiz maker. Generate exactly {$count} MCQ questions.
Difficulty: {$difficulty}
Content: {$context}

Return ONLY a valid JSON array, no markdown, no explanation:
[{"question":"string","options":["A","B","C","D"],"answer":0,"explanation":"string","topic":"string"}]

Rules:
- answer = zero-based index 0-3
- exactly 4 options per question
- 1-2 sentence explanation
- vary correct answer positions across questions
- questions must be diverse and test different aspects
PROMPT;
    }

    // ── Parse & clean AI JSON response ──────────────────────────────────────
    private function parseQuestions(string $raw, string $source): array
    {
        $cleaned = preg_replace('/```json|```/', '', $raw);
        $cleaned = trim($cleaned);
        $data    = json_decode($cleaned, true);

        if (!$data) {
            throw new \Exception('AI returned invalid JSON. Please try again.');
        }

        $arr = isset($data['questions']) ? $data['questions'] : $data;
        if (!is_array($arr)) {
            throw new \Exception('Unexpected AI response format.');
        }

        return array_map(fn($q) => array_merge($q, ['source' => $source]), $arr);
    }

    // ── Core Groq API call ───────────────────────────────────────────────────
    private function callGroq(array $messages, float $temperature = 0.7, int $maxTokens = 4096, bool $vision = false): string
    {
        $model = $vision ? $this->visionModel : $this->model;

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->withoutVerifying()          // Fix: cURL error 60 — SSL cert on local/shared host
            ->post($this->baseUrl, [
                'model'       => $model,
                'temperature' => $temperature,
                'max_tokens'  => $maxTokens,
                'messages'    => $messages,
            ]);

        if (!$response->successful()) {
            Log::error('Groq API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('AI service error: ' . ($response->json('error.message') ?? 'Unknown error'));
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    // ── Generate from Topic ─────────────────────────────────────────────────
    public function generateFromTopic(string $topic, string $subject, int $count, string $difficulty): array
    {
        $context  = "Subject: {$subject}\nTopic: {$topic}";
        $messages = [
            ['role' => 'system', 'content' => 'You are an expert educational quiz generator. Respond with valid JSON only.'],
            ['role' => 'user',   'content' => $this->buildPrompt($context, $count, $difficulty)],
        ];

        $raw = $this->callGroq($messages, 0.7);
        return $this->parseQuestions($raw, 'topic');
    }

    // ── Generate from PDF text ───────────────────────────────────────────────
    public function generateFromPdfText(string $text, int $count, string $difficulty): array
    {
        $context  = substr($text, 0, 4000);
        $messages = [
            ['role' => 'system', 'content' => 'Expert educational quiz generator. Valid JSON only.'],
            ['role' => 'user',   'content' => $this->buildPrompt($context, $count, $difficulty)],
        ];

        $raw = $this->callGroq($messages, 0.6);
        return $this->parseQuestions($raw, 'pdf');
    }

    // ── Generate from Image (base64) ─────────────────────────────────────────
    public function generateFromImage(string $base64, string $mimeType, int $count): array
    {
        $prompt = <<<PROMPT
This is an educational diagram. Identify what it shows and generate exactly {$count} MCQ questions.
Return ONLY valid JSON: {"detectedLabel":"string","questions":[{"question":"string","options":["A","B","C","D"],"answer":0,"explanation":"string","topic":"string"}]}
If NOT educational, set detectedLabel to "non_educational" and return empty questions array.
PROMPT;

        $messages = [[
            'role'    => 'user',
            'content' => [
                ['type' => 'text',      'text'      => $prompt],
                ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64}"]],
            ],
        ]];

        $raw    = $this->callGroq($messages, 0.7, 4096, true);
        $parsed = json_decode(preg_replace('/```json|```/', '', trim($raw)), true);

        if (!$parsed) {
            throw new \Exception('AI returned invalid JSON for image.');
        }

        if (($parsed['detectedLabel'] ?? '') === 'non_educational') {
            throw new \Exception('Not an educational diagram. Please upload biology, physics, chemistry, or geography diagrams.');
        }

        $questions = array_map(fn($q) => array_merge($q, ['source' => 'image']), $parsed['questions'] ?? []);
        return ['questions' => $questions, 'label' => $parsed['detectedLabel'] ?? 'diagram'];
    }

    // ── Generate by Class + Subject (standard curriculum) ───────────────────
    public function generateStandard(string $subject, string $class, int $count, string $difficulty): array
    {
        $context  = "Indian school curriculum - Class {$class} {$subject}. Generate curriculum-appropriate questions.";
        $messages = [
            ['role' => 'system', 'content' => 'Expert Indian school curriculum quiz generator. Valid JSON only.'],
            ['role' => 'user',   'content' => $this->buildPrompt($context, $count, $difficulty)],
        ];

        $raw = $this->callGroq($messages, 0.7);
        return $this->parseQuestions($raw, 'standard');
    }

    // ── AI Tutor Chat ────────────────────────────────────────────────────────
    public function tutorChat(string $message, string $subject, array $history): string
    {
        $checkRaw = $this->callGroq([
            ['role' => 'system', 'content' => 'Classify as "allowed" (study/academic) or "blocked" (anything else). Reply ONLY: allowed OR blocked'],
            ['role' => 'user',   'content' => $message],
        ], 0.0, 10);

        if (trim(strtolower($checkRaw)) === 'blocked') {
            return "⚠️ I'm your **Study Tutor** — I only help with academic topics like Math, Science, History, and more. Please ask a study-related question! 📚";
        }

        $systemPrompt = <<<SYS
You are an expert AI Study Tutor for students (Class 6 to College).
Subject focus: {$subject}
Be friendly, encouraging, use simple language, examples, emojis occasionally.
For math/science: show step-by-step solutions. For theory: use bullet points.
ONLY answer academic questions. Max 300 words. Format nicely with line breaks.
SYS;

        $recentHistory = array_slice($history, -6);
        $msgs = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($recentHistory as $h) {
            $msgs[] = ['role' => $h['role'] === 'user' ? 'user' : 'assistant', 'content' => $h['text']];
        }
        $msgs[] = ['role' => 'user', 'content' => $message];

        return $this->callGroq($msgs, 0.7, 600);
    }

    // ── Performance Suggestions ──────────────────────────────────────────────
    public function suggestions(array $weakSubjects): string
    {
        $data   = empty($weakSubjects) ? ['All subjects performing well'] : $weakSubjects;
        $prompt = "Student performance data: " . json_encode($data) . "\nProvide a short, encouraging, personalized study suggestion (2-3 sentences, friendly tone, with emoji). Be specific about what to study.";

        return $this->callGroq([
            ['role' => 'user', 'content' => $prompt],
        ], 0.8, 150);
    }
}