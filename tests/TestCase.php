<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * The pre-access-control academic tests create an actor named Academic Admin.
     * Keep those regression tests realistic by granting that explicit test actor
     * the Academic Admin role. Tests for restricted roles assign a role first.
     */
    public function actingAs(Authenticatable $user, $guard = null): static
    {
        if ($user instanceof User && $user->exists && ! $user->roles()->exists()) {
            app(AccessControlSeeder::class)->run();
            $user->syncRoles(['Academic Admin']);
        }

        return parent::actingAs($user, $guard);
    }
}
