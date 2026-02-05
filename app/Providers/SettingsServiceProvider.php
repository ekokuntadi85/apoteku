<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('settings')) {
            try {
                $settings = Setting::all()->keyBy('key')->map(function ($setting) {
                    return $setting->value;
                });
                config(['settings' => $settings->toArray()]);

                if ($settings->has('app_name')) {
                    config(['app.name' => $settings->get('app_name')]);
                }
            } catch (\Exception $e) {
                // Fails gracefully if the database is not connected
            }
        }
    }
}
