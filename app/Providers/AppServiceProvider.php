<?php

namespace App\Providers;

use App\Models\Result\SemesterResult;
use App\Policies\Result\SemesterResultPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Gate::policy(SemesterResult::class, SemesterResultPolicy::class);
    }
}
