<?php

return [
    'moderation' => [
        'banned_words' => [
            'spam',
            'shit',
            'fuck',
        ],
        'rate_limit' => [
            'max_attempts' => 5,
            'decay_seconds' => 60,
        ],
    ],
];
