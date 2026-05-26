<?php

namespace Modules\User\Controllers;

use Modules\User\DataTransferObjects\UserData;
use Modules\User\Models\User;
use Spatie\LaravelData\PaginatedDataCollection;

class UserController
{
    public function index(): PaginatedDataCollection
    {
        $users = User::query()
            ->with(['currentOrganization', 'organizations'])
            ->orderBy('name')
            ->paginate(15);

        return UserData::collect(
            $users->through(fn (User $user): UserData => UserData::fromModel($user)),
            PaginatedDataCollection::class
        );
    }
}
