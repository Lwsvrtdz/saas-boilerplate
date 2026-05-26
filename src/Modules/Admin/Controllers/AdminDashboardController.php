<?php

namespace Modules\Admin\Controllers;

use Modules\Access\Models\Role;
use Modules\Admin\DataTransferObjects\AdminMetricsData;
use Modules\Admin\DataTransferObjects\AdminOverviewData;
use Modules\Tenancy\Models\Organization;
use Modules\User\Models\User;

class AdminDashboardController
{
    public function show(): AdminOverviewData
    {
        return new AdminOverviewData(
            metrics: new AdminMetricsData(
                users: User::query()->count(),
                organizations: Organization::query()->count(),
                roles: Role::query()->count(),
            ),
        );
    }
}
