<?php

namespace Modules\Tenancy\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Shared\Controllers\ApiController;
use Modules\Shared\Exceptions\ApiException;
use Modules\Tenancy\DataTransferObjects\AcceptOrganizationInvitationData;
use Modules\Tenancy\DataTransferObjects\CreateOrganizationInvitationData;
use Modules\Tenancy\DataTransferObjects\OrganizationData;
use Modules\Tenancy\DataTransferObjects\OrganizationInvitationData;
use Modules\Tenancy\Models\Organization;
use Modules\Tenancy\Models\OrganizationInvitation;
use Modules\Tenancy\Services\OrganizationInvitationService;
use Modules\User\Models\User;
use Spatie\LaravelData\DataCollection;

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
            ->get();

        return $this->success(
            OrganizationInvitationData::collect(
                $invitations->map(
                    fn (OrganizationInvitation $invitation): OrganizationInvitationData => OrganizationInvitationData::fromModel(
                        $invitation
                    )
                ),
                DataCollection::class
            )
        );
    }

    public function store(CreateOrganizationInvitationData $data, Request $request): JsonResponse
    {
        $payload = $this->invitationService->create(
            $this->currentOrganization($request),
            $this->authenticatedUser($request),
            $data,
        );

        return $this->created([
            'invitation' => OrganizationInvitationData::fromModel($payload['invitation']),
            'token' => $payload['plain_text_token'],
        ], 'Invitation created.');
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->invitationService->delete(
            $this->currentOrganization($request),
            $this->authenticatedUser($request),
            $id,
        );

        return $this->noContent();
    }

    public function accept(AcceptOrganizationInvitationData $data, Request $request): JsonResponse
    {
        $invitation = $this->invitationService->accept(
            $this->authenticatedUser($request),
            $data,
        );

        return $this->success([
            'invitation' => OrganizationInvitationData::fromModel($invitation),
            'organization' => OrganizationData::fromModel($invitation->organization),
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
