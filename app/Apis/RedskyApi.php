<?php

namespace App\Apis;

class RedskyApi
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getProduct($productId)
    {
        // TODO: Implement getProduct method
    }

    public function updateProduct($productId, $data)
    {
        // TODO: Implement updateProduct method
    }


    function extractAlternateIds($users)
    {
        $results = [];
        // Loop through each user
        foreach ($users as $user) {
            $userAltIds = [];
            // Check if 'alternateIdList' exists and is not empty
            if (!empty($user['alternateIdList'])) {
                // Loop through each alternate ID in the list
                foreach ($user['alternateIdList'] as $altId) {
                    $userAltIds[] = $altId['alternateId'];
                }
                // Create a string of comma-separated alternate IDs
                $results[$user['email']] = implode(', ', $userAltIds);
            } else {
                // Friendly notice if no alternate IDs are found
                $results[$user['email']] = 'No alternate IDs found';
            }
        }
        return $results;
    }

    
}
