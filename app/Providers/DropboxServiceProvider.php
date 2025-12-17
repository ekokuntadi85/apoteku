<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;
use App\Services\DropboxRefreshableTokenProvider;

class DropboxServiceProvider extends ServiceProvider
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
        Storage::extend('dropbox', function ($app, $config) {
            // Check if using OAuth 2.0 refresh token (permanent solution)
            if (isset($config['refresh_token']) && !empty($config['refresh_token'])) {
                $tokenProvider = new DropboxRefreshableTokenProvider(
                    $config['app_key'],
                    $config['app_secret'],
                    $config['refresh_token']
                );
                
                $client = new DropboxClient($tokenProvider);
            } else {
                // Fallback to access token (temporary, expires in 4 hours)
                $client = new DropboxClient($config['authorization_token']);
            }

            $adapter = new DropboxAdapter($client);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
