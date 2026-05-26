<?php

namespace Modules\Tenancy\DataTransferObjects;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Shared\DataTransferObjects\DataTransferObject;

class AcceptedOrganizationInvitationData extends DataTransferObject
{
    public function __construct(
        public OrganizationInvitationData $invitation,
        public OrganizationData $organization,
    ) {
    }

    protected function calculateResponseStatus(Request $request): int
    {
        return Response::HTTP_OK;
    }
}
