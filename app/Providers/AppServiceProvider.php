<?php

namespace App\Providers;

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
        // Fallback for servers where storage/framework/views is not writable.
        // Without this, Blade's view compiler hits tempnam() and can render
        // a fatal error before login is even possible. If the configured
        // compiled-view dir is missing/unwritable, switch compiled views to
        // a per-request-safe directory inside PHP's temp dir.
        $configuredPath = config('view.compiled');
        $compiledDir = is_string($configuredPath) ? $configuredPath : storage_path('framework/views');

        if (! is_dir($compiledDir) || ! is_writable($compiledDir)) {
            $fallback = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ecom_compiled_views';

            if (! is_dir($fallback)) {
                @mkdir($fallback, 0775, true);
            }

            if (is_dir($fallback) && is_writable($fallback)) {
                config(['view.compiled' => $fallback]);
            }
        }
    }
}
