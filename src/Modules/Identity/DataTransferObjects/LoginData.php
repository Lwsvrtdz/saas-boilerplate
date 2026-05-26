<?php

namespace Modules\Identity\DataTransferObjects;

use Modules\Shared\DataTransferObjects\DataTransferObject;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

class LoginData extends DataTransferObject
{
    public function __construct(
        #[Email]
        public string $email,
        public string $password,
        #[MapInputName('device_name')]
        #[Nullable, Max(100)]
        public string $deviceName = 'nuxt-client',
    ) {
    }

    /**
     * @param array<string, mixed> $properties
     * @return array<string, mixed>
     */
    public static function prepareForPipeline(array $properties): array
    {
        $properties['device_name'] ??= 'nuxt-client';

        if ($properties['device_name'] === null) {
            $properties['device_name'] = 'nuxt-client';
        }

        return $properties;
    }
}
