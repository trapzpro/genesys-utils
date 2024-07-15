<?

namespace App\Services;

use GuzzleHttp\Client;

class GenesysApiService
{
    protected $client;
    protected $baseUrl;
    protected $accessToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = env('GENESYS_API_BASE_URL');
        $this->accessToken = $this->getAccessToken();
    }

    protected function getAccessToken()
    {
        // Implement Genesys authentication logic here
        // Return the access token
    }

    public function getUsers()
    {
        $response = $this->client->get("{$this->baseUrl}/api/v2/users", [
            'headers' => [
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
