<?php

namespace App\Livewire;

use Livewire\Component;
use GuzzleHttp\Client;

class GenesysOauth extends Component
{
    public $clientId;
    public $clientSecret;
    public $users = [];

    public function getAccessToken($clientId, $clientSecret)
    {
        $client = new Client();
        $response = $client->post('https://login.mypurecloud.com/oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        return $body['access_token'];
    }

    public function fetchUsers($accessToken)
    {
        $client = new Client();
        $response = $client->get('https://api.mypurecloud.com/api/v2/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        return $body['entities'];
    }

    public function retrieveUsers()
    {
        // if ($this->clientId && $this->clientSecret) {
        //     $accessToken = $this->getAccessToken($this->clientId, $this->clientSecret);
        //     $this->users = $this->fetchUsers($accessToken);
        // }

        // get users from database/data/users.json
        $this->users = json_decode(file_get_contents(database_path('data/users.json')), true)['entities'];
    }

    public function render()
    {
        return view('livewire.genesys-oauth');
    }
}
