<?php

namespace Modules\Identity\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Modules\Identity\DataTransferObjects\RegisterData;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toDto(): RegisterData
    {
        return new RegisterData(
            name: (string) $this->input('name'),
            email: (string) $this->input('email'),
            password: (string) $this->input('password'),
            deviceName: (string) $this->input('device_name', 'nuxt-client'),
        );
    }
}
