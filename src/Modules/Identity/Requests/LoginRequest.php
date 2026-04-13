<?php

namespace Modules\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Identity\DataTransferObjects\LoginData;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toDto(): LoginData
    {
        return new LoginData(
            email: (string) $this->input('email'),
            password: (string) $this->input('password'),
            deviceName: (string) $this->input('device_name', 'nuxt-client'),
        );
    }
}
