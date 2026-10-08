<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{id}.notifications', function (User $user, int $id) {
    abort_unless((int) $user->getAuthIdentifier() === $id, 403);

    return true;
});
