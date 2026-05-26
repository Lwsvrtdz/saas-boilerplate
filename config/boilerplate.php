<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
    'organization_header' => env('TENANCY_ORGANIZATION_HEADER', 'X-Organization'),
    'auth_rate_limit_per_minute' => (int) env('AUTH_RATE_LIMIT_PER_MINUTE', 5),
    'api_token_ttl_minutes' => (int) env('API_TOKEN_TTL_MINUTES', 10080),
];
