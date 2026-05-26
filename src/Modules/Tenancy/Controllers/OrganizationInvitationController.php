<?php

namespace Modules\Tenancy\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Controllers\ApiController;
use Modules\Shared\Exceptions\ApiException;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\DataTransferObjects\OrganizationInvitationData;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Requests\AcceptOrganizationInvitationRequest;
use Modules\Tenancy\Requests\CreateOrganizationInvitationRequest;
use Modules\Tenancy\Services\OrganizationInvitationService;
use Modules\User\Models\User;

class OrganizationInvitationController extends ApiController
{
    public function __construct(
        private readonly OrganizationInvitationService $invitationService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $organization = $this->currentOrganization($request);
        $user = $this->authenticatedUser($request);

        $this->invitationService->ensureCanManageInvitations($user, $organization);

        $invitations = $organization
            ->invitations()
            ->latest()
            ->get()
            ->map(fn ($invitation): array => OrganizationInvitationData::fromModel($invitation)->toArray())
            ->all();

        return $this->success($invitations);
    }

    public function store(CreateOrganizationInvitationRequest $request): JsonResponse
    {
        $payload = $this->invitationService->create(
            $this->currentOrganization($request),
            $this->authenticatedUser($request),
            $request->toDto(),
        );

        return $this->created([
            'invitation' => OrganizationInvitationData::fromModel($payload['invitation'])->toArray(),
            'token' => $payload['plain_text_token'],
        ], 'Invitation created.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->invitationService->delete(
            $this->currentOrganization($request),
            $this->authenticatedUser($request),
            $id,
        );

        return $this->noContent();
    }

    public function accept(AcceptOrganizationInvitationRequest $request): JsonResponse
    {
        $invitation = $this->invitationService->accept(
            $this->authenticatedUser($request),
            $request->toDto(),
        );

        return $this->success([
            'invitation' => OrganizationInvitationData::fromModel($invitation)->toArray(),
            'organization' => OrganizationData::fromModel($invitation->organization)->toArray(),
        ], 'Invitation accepted.');
    }

    protected function currentOrganization(Request $request): Organization
    {
        $organization = Organization::current()
            ?? $request->attributes->get('current_organization');

        if (! $organization instanceof Organization) {
            throw ApiException::notFound('Organization context was not found.');
        }

        return $organization;
    }

    protected function authenticatedUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw ApiException::unauthorized();
        }

        return $user;
    }
}
