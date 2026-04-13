<?php

namespace Modules\User\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Shared\Controllers\ApiController;
use Modules\Shared\Responses\ApiResponse;
use Modules\User\DataTransferObjects\UserData;
use Modules\User\Models\User;

class UserController extends ApiController
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->with(['currentOrganization', 'organizations'])
            ->orderBy('name')
            ->paginate(15);

        return ApiResponse::paginated(
            $users,
            $users->getCollection()
                ->map(fn (User $user): array => UserData::fromModel($user)?->toArray() ?? [])
                ->all()
        );
    }
}
