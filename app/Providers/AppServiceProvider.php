<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
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
        Gate::define('manage-settings', fn (User $user): bool => $user->active_status
            && $user->role?->name_role === Role::ADMIN);

        Gate::define('access-module', fn (User $user, string $module, string $action): bool => $user->active_status
            && ($user->role?->permissions->firstWhere('module_name', $module)?->{'can_'.$action} ?? false));
    }
}
