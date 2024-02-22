<?php

namespace App\Service\Platform\Lazada;

use App\Entity\Platform\Lazada\LazadaStore;

class LazadaAuthAPI extends LazadaAPIClient
{
    public function getAuthToken(string $body): string
    {
        return hash_hmac('sha256', $this->lazadaKey . $body, $this->lazadaSecret);
    }

    public function getAccessData(string $code): array
    {
        $currentTime = time();
        $response = $this->post('/auth/token/create', ['code' => $code], false);
        if ($response['code'] == 0) {
            $timezone = new \DateTimeZone('Asia/Kuala_Lumpur');
            $data = [
                'seller_id' => null,
                'alt' => [],
                'region' => $response['country'],
                'access_token' => $response['access_token'],
                'access_expires' => new \DateTime('@' . $currentTime + $response['expires_in'], $timezone),
                'refresh_token' => $response['refresh_token'],
                'refresh_expires' => new \DateTime('@' . $currentTime + $response['refresh_expires_in'], $timezone)
            ];
            $storeData = $response['country_user_info'];
            foreach ($storeData as $regionData) :
                if ($regionData['country'] == $data['region'])
                    $data['seller_id'] = $regionData['seller_id'];
                else
                    $data['alt'][] = [
                        'region' => $regionData['country'],
                        'seller_id' => $regionData['seller_id']
                    ];
            endforeach;
            return $data;
        } else throw new \Exception($response['message']);
    }

    public function getStoreData(LazadaStore $store): array
    {
        $this->setStore($store);
        $response = $this->get('/seller/get', []);

        if ($response['code'] == 0) {
            $data = $response['data'];
            return [
                'ref' => $data['seller_id'],
                'name' => $data['name'],
                'status' => $data['status']
            ];
        } else throw new \Exception($response['message']);
    }
}

