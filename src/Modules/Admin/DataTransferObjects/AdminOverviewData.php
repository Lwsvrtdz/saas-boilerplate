<?php

namespace Modules\Admin\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;

class AdminOverviewData extends DataTransferObject
{
    public function __construct(
        public AdminMetricsData $metrics,
    ) {
    }
}
