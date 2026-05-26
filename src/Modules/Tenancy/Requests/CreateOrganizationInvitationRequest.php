<?php

namespace Modules\Tenancy\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Tenancy\DataTransferObjects\CreateOrganizationInvitationData;

class CreateOrganizationInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function toDto(): CreateOrganizationInvitationData
    {
        return new CreateOrganizationInvitationData(
            email: (string) $this->input('email'),
            roleId: $this->filled('role_id') ? (int) $this->input('role_id') : null,
            expiresAt: $this->filled('expires_at') ? (string) $this->input('expires_at') : null,
        );
    }
}
