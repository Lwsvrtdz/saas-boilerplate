<?php

namespace Modules\Admin\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Access\Models\Role;
use Modules\Shared\Controllers\ApiController;
use Modules\Tenancy\Models\Organization;
use Modules\User\Models\User;

class AdminDashboardController extends ApiController
{
    public function show(): JsonResponse
    {
        return $this->success([
            'metrics' => [
                'users' => User::query()->count(),
                'organizations' => Organization::query()->count(),
                'roles' => Role::query()->count(),
            ],
        ]);
    }
}
