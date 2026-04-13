<?php

namespace Modules\Identity\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\Identity\Models\ApiToken;
use Modules\Shared\Exceptions\ApiException;
use Modules\User\Models\User;

class ApiTokenService
{
    /**
     * @param array<int, string> $abilities
     * @return array{plain_text_token: string, token: ApiToken}
     */
    public function issue(User $user, string $name = 'default', array $abilities = ['*']): array
    {
        $plainTextToken = bin2hex(random_bytes(32));

        $token = $user->apiTokens()->create([
            'name' => $name,
            'token_hash' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => now()->addMinutes(config('boilerplate.api_token_ttl_minutes')),
        ]);

        return [
            'plain_text_token' => $plainTextToken,
            'token' => $token,
        ];
    }

    public function resolveUserFromRequest(Request $request): ?User
    {
        $bearerToken = $request->bearerToken();

        if (! is_string($bearerToken) || $bearerToken === '') {
            return null;
        }

        $token = ApiToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $bearerToken))
            ->first();

        if ($token === null) {
            return null;
        }

        if ($token->expires_at instanceof Carbon && $token->expires_at->isPast()) {
            $token->delete();

            return null;
        }

        $token->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('current_api_token', $token);

        return $token->user;
    }

    public function revokeCurrentToken(Request $request): void
    {
        $token = $request->attributes->get('current_api_token');

        if (! $token instanceof ApiToken) {
            throw ApiException::unauthorized('No active API token was found for this request.');
        }

        $token->delete();
    }
}
