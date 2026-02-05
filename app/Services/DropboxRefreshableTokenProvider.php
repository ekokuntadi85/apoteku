<?php

namespace App\Services;

use Spatie\Dropbox\TokenProvider;
use GuzzleHttp\Client;

class DropboxRefreshableTokenProvider implements TokenProvider
{
    protected $appKey;
    protected $appSecret;
    protected $refreshToken;
    protected $accessToken;
    protected $expiresAt;

    public function __construct(string $appKey, string $appSecret, string $refreshToken)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->refreshToken = $refreshToken;
    }

    public function getToken(): string
    {
        // If token is still valid, return it
        if ($this->accessToken && $this->expiresAt > time()) {
            return $this->accessToken;
        }

        // Refresh the token
        $this->refreshAccessToken();

        return $this->accessToken;
    }

    protected function refreshAccessToken(): void
    {
        $client = new Client();
        
        $response = $client->post('https://api.dropbox.com/oauth2/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refreshToken,
            ],
            'auth' => [$this->appKey, $this->appSecret],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->accessToken = $data['access_token'];
        $this->expiresAt = time() + ($data['expires_in'] ?? 14400) - 300; // 5 min buffer

        \Log::info('Dropbox access token refreshed', [
            'expires_in' => $data['expires_in'] ?? 14400,
        ]);
    }
}
