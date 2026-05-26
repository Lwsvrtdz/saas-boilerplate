<?php

namespace Modules\Tenancy\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Access\Models\Role;
use Modules\Access\Services\AuthorizationService;
use Modules\Shared\Exceptions\ApiException;
use Modules\Tenancy\DataTransferObjects\AcceptOrganizationInvitationData;
use Modules\Tenancy\DataTransferObjects\CreateOrganizationInvitationData;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Models\OrganizationInvitation;
use Modules\Tenancy\Models\OrganizationMembership;
use Modules\User\Models\User;

class OrganizationInvitationService
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    /**
     * @return array{invitation: OrganizationInvitation, plain_text_token: string}
     */
    public function create(
        Organization $organization,
        User $inviter,
        CreateOrganizationInvitationData $data,
    ): array {
        $this->ensureCanManageInvitations($inviter, $organization);

        if ($data->roleId !== null) {
            $role = Role::query()
                ->whereKey($data->roleId)
                ->where('scope', 'organization')
                ->first();

            if (! $role instanceof Role) {
                throw ApiException::notFound('Invitation role was not found.');
            }
        }

        $email = Str::lower($data->email);
        $plainTextToken = bin2hex(random_bytes(32));

        try {
            $invitation = DB::transaction(function () use (
                $organization,
                $inviter,
                $data,
                $email,
                $plainTextToken,
            ): OrganizationInvitation {
                $pendingInvitation = OrganizationInvitation::query()
                    ->where('organization_id', $organization->getKey())
                    ->where('email', $email)
                    ->where('pending_marker', true)
                    ->lockForUpdate()
                    ->first();

                if ($pendingInvitation instanceof OrganizationInvitation) {
                    if ($pendingInvitation->expires_at->isFuture()) {
                        throw new ApiException('A pending invitation already exists for this email.');
                    }

                    $pendingInvitation->forceFill([
                        'pending_marker' => null,
                    ])->save();
                }

                return OrganizationInvitation::query()->create([
                    'organization_id' => $organization->getKey(),
                    'email' => $email,
                    'role_id' => $data->roleId,
                    'token_hash' => hash('sha256', $plainTextToken),
                    'invited_by_user_id' => $inviter->getKey(),
                    'pending_marker' => true,
                    'expires_at' => $data->expiresAt !== null
                        ? Carbon::parse($data->expiresAt)
                        : now()->addDays(7),
                ]);
            });
        } catch (QueryException $exception) {
            if ($this->causedByDuplicatePendingInvitation($exception)) {
                throw new ApiException('A pending invitation already exists for this email.');
            }

            throw $exception;
        }

        return [
            'invitation' => $invitation,
            'plain_text_token' => $plainTextToken,
        ];
    }

    public function delete(Organization $organization, User $user, int $invitationId): void
    {
        $this->ensureCanManageInvitations($user, $organization);

        $invitation = OrganizationInvitation::query()
            ->where('organization_id', $organization->getKey())
            ->whereKey($invitationId)
            ->first();

        if (! $invitation instanceof OrganizationInvitation) {
            throw ApiException::notFound('Invitation was not found.');
        }

        $invitation->delete();
    }

    public function accept(User $user, AcceptOrganizationInvitationData $data): OrganizationInvitation
    {
        return DB::transaction(function () use ($user, $data): OrganizationInvitation {
            $invitation = OrganizationInvitation::query()
                ->with(['organization', 'role'])
                ->where('token_hash', hash('sha256', $data->token))
                ->where('pending_marker', true)
                ->whereNull('accepted_at')
                ->first();

            if (! $invitation instanceof OrganizationInvitation) {
                throw ApiException::notFound('Invitation was not found.');
            }

            if ($invitation->expires_at->isPast()) {
                throw new ApiException('Invitation has expired.');
            }

            if (Str::lower($user->email) !== Str::lower($invitation->email)) {
                throw ApiException::forbidden('This invitation belongs to a different email address.');
            }

            $role = $invitation->role ?? Role::query()->where('slug', 'member')->firstOrFail();

            OrganizationMembership::query()->firstOrCreate(
                [
                    'organization_id' => $invitation->organization_id,
                    'user_id' => $user->getKey(),
                ],
                [
                    'title' => $role->name,
                    'is_owner' => $role->slug === 'owner',
                ],
            );

            $this->authorizationService->assignRole($user, $role, $invitation->organization);

            $invitation->forceFill([
                'accepted_at' => now(),
                'pending_marker' => null,
            ])->save();

            $acceptedInvitation = $invitation->fresh(['organization', 'role']);

            if (! $acceptedInvitation instanceof OrganizationInvitation) {
                throw ApiException::notFound('Invitation was not found.');
            }

            return $acceptedInvitation;
        });
    }

    public function ensureCanManageInvitations(User $user, Organization $organization): void
    {
        $canManageOrganization = $this->authorizationService->userHasPermission(
            $user,
            'organization.manage',
            $organization,
        );

        $canManageUsers = $this->authorizationService->userHasPermission(
            $user,
            'users.manage',
            $organization,
        );

        if (! $canManageOrganization && ! $canManageUsers) {
            throw ApiException::forbidden('Managing organization invitations requires organization or user management access.');
        }
    }

    protected function causedByDuplicatePendingInvitation(QueryException $exception): bool
    {
        $errorInfo = $exception->errorInfo;

        if (! is_array($errorInfo)) {
            return false;
        }

        return ($errorInfo[0] ?? null) === '23000'
            && str_contains((string) ($errorInfo[2] ?? ''), 'organization_invitations_unique_pending');
    }
}
