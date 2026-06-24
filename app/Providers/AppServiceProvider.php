<?php

namespace App\Providers;

use App\Models\FileRequest;
use App\Policies\FileRequestPolicy;
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
        Gate::policy(FileRequest::class, FileRequestPolicy::class);
    }
}
