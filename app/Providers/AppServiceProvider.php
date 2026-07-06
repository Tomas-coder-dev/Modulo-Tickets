<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::define('viewTicketBoard', function ($user) {
            return in_array($user->role, ['admin', 'ti']);
        });

        Gate::define('manageTicket', function ($user) {
            return in_array($user->role, ['admin', 'ti']);
        });
    }
}