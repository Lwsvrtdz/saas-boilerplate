<?php

namespace Modules\Tenancy\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Controllers\ApiController;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\Models\Organization;

class OrganizationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $organizations = $request->user()
            ?->organizations()
            ->orderBy('name')
            ->get()
            ->map(fn (Organization $organization): array => OrganizationData::fromModel($organization)->toArray())
            ->all() ?? [];

        return $this->success($organizations);
    }

    public function current(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('current_organization');

        return $this->success(
            $organization instanceof Organization ? OrganizationData::fromModel($organization)->toArray() : null
        );
    }
}
