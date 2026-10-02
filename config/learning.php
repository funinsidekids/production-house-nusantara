<?php

return [
    'api_version' => env('API_VERSION', '1.0.0'),
    'content_version' => env('CONTENT_VERSION', '1'),
    'database_version' => env('DATABASE_VERSION', '1'),
    'app_min_version' => env('APP_MIN_VERSION', '1.0.0'),

    // Question engine limits
    'max_random_questions' => (int) env('MAX_RANDOM_QUESTIONS', 50),

    // AI cost control
    'ai' => [
        'enabled' => env('AI_ENABLED', true),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'max_output_tokens' => (int) env('AI_MAX_OUTPUT_TOKENS', 4096),
        'temperature' => (float) env('AI_TEMPERATURE', 0.4),
        'timeout_seconds' => (int) env('AI_TIMEOUT', 60),
        'max_questions_per_request' => (int) env('AI_MAX_QUESTIONS_PER_REQUEST', 20),
        'max_input_chars' => (int) env('AI_MAX_INPUT_CHARS', 4000),
        'history_messages' => (int) env('AI_HISTORY_MESSAGES', 10),
        // When true, AI-generated questions are saved as pending and require admin review.
        'require_moderation' => env('AI_REQUIRE_MODERATION', true),
    ],

    // Remedial engine thresholds (configurable per prompt #26)
    'remedial' => [
        'weak_topic_accuracy_threshold' => (float) env('REMEDIAL_ACCURACY_THRESHOLD', 60),
        'min_attempts_for_evaluation' => (int) env('REMEDIAL_MIN_ATTEMPTS', 5),
        'remedial_question_count' => (int) env('REMEDIAL_QUESTION_COUNT', 10),
    ],

    // Sync tuning
    'sync' => [
        'chunk_size' => (int) env('SYNC_CHUNK_SIZE', 500),
        'max_push_changes' => (int) env('SYNC_MAX_PUSH_CHANGES', 2000),
    ],

    // Rate limits (requests per minute unless stated otherwise)
    'rate_limits' => [
        'api' => (int) env('RATE_LIMIT_API', 60),
        'auth' => (int) env('RATE_LIMIT_AUTH', 10),
        'ai' => (int) env('RATE_LIMIT_AI', 10),
        'sync' => (int) env('RATE_LIMIT_SYNC', 30),
    ],

    'tryout' => [
        // Grace period after expiry during which a submit is still accepted (seconds)
        'submit_grace_seconds' => (int) env('TRYOUT_SUBMIT_GRACE_SECONDS', 120),
    ],
];
