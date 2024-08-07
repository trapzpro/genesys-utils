<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;
use Illuminate\Http\Client\RequestException;

class E911AuthService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private int $maxRetries = 3;

    public function __construct()
    {
        $this->baseUrl = config('services.e911.base_url');
        $this->username = config('services.e911.username');
        $this->password = config('services.e911.password');
    }

    public function getToken(): string
    {
        $cachedToken = Cache::get('e911_auth_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        return $this->authenticateWithRetry();
    }

    private function authenticateWithRetry(): string
    {
        $retries = 0;
        $delay = 1000; // Start with a 1-second delay

        while ($retries < $this->maxRetries) {
            try {
                return $this->authenticate();
            } catch (RequestException $e) {
                $retries++;
                if ($retries >= $this->maxRetries) {
                    throw $e;
                }

                // Log the retry attempt
                logger()->warning("E911 Authentication retry {$retries}/{$this->maxRetries}: " . $e->getMessage());

                // Exponential backoff
                usleep($delay * 1000); // Convert to microseconds
                $delay *= 2; // Double the delay for next attempt
            }
        }

        throw new Exception('Failed to authenticate after multiple attempts');
    }

    private function authenticate(): string
    {
        $response = Http::post("{$this->baseUrl}/auth/token", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($response->successful()) {
            $token = $response->json('token');
            $this->cacheToken($token);
            return $token;
        } else {
            throw new RequestException($response);
        }
    }

    private function cacheToken(string $token): void
    {
        Cache::put('e911_auth_token', $token, now()->addMinutes(55));
    }

    public function refreshToken(): string
    {
        Cache::forget('e911_auth_token');
        return $this->authenticateWithRetry();
    }

    public function withToken(callable $callback)
    {
        try {
            $token = $this->getToken();
            return $callback($token);
        } catch (RequestException $e) {
            if ($e->response->status() === 401) {
                // Token might be expired, try to refresh
                $token = $this->refreshToken();
                return $callback($token);
            }
            throw $e;
        }
    }
}
