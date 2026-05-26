<?php

namespace Modules\Admin\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;

class AdminMetricsData extends DataTransferObject
{
    public function __construct(
        public int $users,
        public int $organizations,
        public int $roles,
    ) {
    }
}
