<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
    'organization_header' => env('TENANCY_ORGANIZATION_HEADER', 'X-Organization'),
    'api_token_ttl_minutes' => (int) env('API_TOKEN_TTL_MINUTES', 10080),
];
