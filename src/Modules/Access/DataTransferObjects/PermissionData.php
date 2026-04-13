<?php

namespace Modules\Access\DataTransferObjects;

use Modules\Access\Models\Permission;
use Modules\Shared\DataTransferObjects\DataTransferObject;

class PermissionData extends DataTransferObject
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $description,
    ) {
    }

    public static function fromModel(Permission $permission): self
    {
        return new self(
            id: $permission->getKey(),
            name: $permission->name,
            slug: $permission->slug,
            description: $permission->description,
        );
    }
}
