<?php 
// app/Services/GooglePlayService.php

namespace App\Services;

use Google_Client;
use Google_Service_AndroidPublisher;

class GooglePlayService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setAuthConfig(env('GOOGLE_PLAY_API_KEY_PATH',"../meet-now-service-account-google-play.json"));
        $this->client->addScope(Google_Service_AndroidPublisher::ANDROIDPUBLISHER);
    }

    public function verifyPurchase($productId, $purchaseToken)
    {
        $service = new Google_Service_AndroidPublisher($this->client);
        $packageName = env('GOOGLE_PLAY_PACKAGE_NAME','live.meetnow.meetnow');
        try {
            $response = $service->purchases_subscriptions->get($packageName, $productId, $purchaseToken);
            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
