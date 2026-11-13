<?php

return [
    // Add only verified store policies/FAQ facts here or through the server's
    // CHATBOT_KNOWLEDGE_BASE environment variable. Never include customer data.
    'knowledge_base' => env('CHATBOT_KNOWLEDGE_BASE', ''),
];
