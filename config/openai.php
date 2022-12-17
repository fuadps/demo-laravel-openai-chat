<?php

return [
    'key' => env('OPENAI_API_KEY'),
    'options' => [
        'model' => env('OPENAI_MODEL', 'text-davinci-003'),
        'max_tokens' => 150,
        'temperature' => 0
    ]
];
