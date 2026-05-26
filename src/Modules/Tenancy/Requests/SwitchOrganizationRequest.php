<?php

namespace Modules\Tenancy\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Tenancy\DataTransferObjects\SwitchOrganizationData;

class SwitchOrganizationRequest extends FormRequest
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
            'organization_id' => ['nullable', 'integer', 'required_without:slug'],
            'slug' => ['nullable', 'string', 'max:255', 'required_without:organization_id'],
        ];
    }

    public function toDto(): SwitchOrganizationData
    {
        return new SwitchOrganizationData(
            organizationId: $this->filled('organization_id') ? (int) $this->input('organization_id') : null,
            slug: $this->filled('slug') ? (string) $this->input('slug') : null,
        );
    }
}
