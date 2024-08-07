<?php

// Configuration
$config = [
    'base_url' => 'https://api.redsky.com/v1', // Replace with actual API base URL
    'username' => 'your_username',
    'password' => 'your_password',
    'client_id' => 'your_client_id', // If required
    'client_secret' => 'your_client_secret', // If required
];

// Function to get an access token
function getAccessToken($config)
{
    $url = $config['base_url'] . '/auth/token';
    $data = [
        'grant_type' => 'password',
        'username' => $config['username'],
        'password' => $config['password'],
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
    ];

    $response = makeApiRequest($url, 'POST', $data);
    return $response['access_token'];
}

// Function to refresh the access token
function refreshAccessToken($config, $refresh_token)
{
    $url = $config['base_url'] . '/auth/token';
    $data = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $refresh_token,
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
    ];

    $response = makeApiRequest($url, 'POST', $data);
    return $response['access_token'];
}

// Function to make API requests
function makeApiRequest($url, $method = 'GET', $data = null, $headers = [])
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    }

    if (!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        throw new Exception("API request failed with status code: $httpCode");
    }
}

// Function to get device users
function getDeviceUsers($config, $access_token)
{
    $url = $config['base_url'] . '/deviceusers'; // Replace with actual endpoint
    $headers = ["Authorization: Bearer $access_token"];
    return makeApiRequest($url, 'GET', null, $headers);
}

// Function to write device users to CSV
function writeDeviceUsersToCSV($device_users, $filename)
{
    $file = fopen($filename, 'w');

    // Write CSV header
    fputcsv($file, array_keys($device_users[0]));

    // Write data rows
    foreach ($device_users as $user) {
        fputcsv($file, $user);
    }

    fclose($file);
}

// Main execution
try {
    // Get initial access token
    $access_token = getAccessToken($config);

    // Get device users
    $device_users = getDeviceUsers($config, $access_token);

    // Write device users to CSV
    writeDeviceUsersToCSV($device_users, 'device_users.csv');

    echo "Device users have been successfully retrieved and written to device_users.csv\n";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
