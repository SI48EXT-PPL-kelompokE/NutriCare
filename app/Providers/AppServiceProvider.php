<?php

namespace App\Providers;

use App\Contracts\Paket4\NakesLookup;
use App\Adapters\Paket4\EloquentNakesLookup;
use App\Models\JadwalPosyandu;
use App\Policies\JadwalPosyanduPolicy;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(JadwalPosyandu::class, JadwalPosyanduPolicy::class);
    }
}
