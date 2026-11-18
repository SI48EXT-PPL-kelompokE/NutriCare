<?php

namespace App\Providers;

use App\Contracts\Paket4\NakesLookup;
use App\Adapters\Paket4\EloquentNakesLookup;
use App\Models\JadwalPosyandu;
use App\Models\Role;
use App\Models\User;
use App\Policies\JadwalPosyanduPolicy;
use App\Services\ZScoreService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NakesLookup::class, EloquentNakesLookup::class);
        
        // ZScoreService sebagai singleton untuk caching optimal
        $this->app->singleton(ZScoreService::class);
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

        Gate::define('view-clinical-records', fn (User $user): bool => $user->role?->name_role !== Role::RESIDENT);

        Gate::define('view-balita-identity', function (User $user): bool {
            return $user->active_status && $user->role?->permissions()
                ->where('module_name', 'balita')
                ->where('can_view', true)
                ->exists();
        });

        Gate::policy(JadwalPosyandu::class, JadwalPosyanduPolicy::class);
    }
}
