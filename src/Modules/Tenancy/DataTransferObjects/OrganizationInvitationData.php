<?php

namespace Modules\Tenancy\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Modules\Tenancy\Models\OrganizationInvitation;

class OrganizationInvitationData extends DataTransferObject
{
    public function __construct(
        public int $id,
        public int $organizationId,
        public string $email,
        public ?int $roleId,
        public int $invitedByUserId,
        public ?string $acceptedAt,
        public string $expiresAt,
    ) {
    }

    public static function fromModel(OrganizationInvitation $invitation): self
    {
        return new self(
            id: $invitation->getKey(),
            organizationId: $invitation->organization_id,
            email: $invitation->email,
            roleId: $invitation->role_id,
            invitedByUserId: $invitation->invited_by_user_id,
            acceptedAt: $invitation->accepted_at?->toISOString(),
            expiresAt: $invitation->expires_at->toISOString(),
        );
    }
}
