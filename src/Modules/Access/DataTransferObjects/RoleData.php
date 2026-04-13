<?php

namespace Modules\Access\DataTransferObjects;

use Modules\Access\Models\Role;
use Modules\Shared\DataTransferObjects\DataTransferObject;

class RoleData extends DataTransferObject
{
    /**
     * @param array<int, string> $permissions
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $scope,
        public array $permissions,
    ) {
    }

    public static function fromModel(Role $role): self
    {
        $role->loadMissing('permissions');

        return new self(
            id: $role->getKey(),
            name: $role->name,
            slug: $role->slug,
            scope: $role->scope,
            permissions: $role->permissions->pluck('slug')->all(),
        );
    }
}
