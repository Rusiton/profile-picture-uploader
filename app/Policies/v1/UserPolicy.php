<?php

namespace App\Policies\v1;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function modify(User $authenticatedUser, User $user) {
        return $authenticatedUser->token === $user->token
            ? Response::allow()
            : Response::deny();
    }
}
