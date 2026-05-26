<?php

namespace Modules\Tenancy\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Tenancy\DataTransferObjects\AcceptOrganizationInvitationData;

class AcceptOrganizationInvitationRequest extends FormRequest
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
            'token' => ['required', 'string', 'size:64'],
        ];
    }

    public function toDto(): AcceptOrganizationInvitationData
    {
        return new AcceptOrganizationInvitationData(
            token: (string) $this->input('token'),
        );
    }
}
