<?php

namespace App\Service\Platform\Lazada;

use App\Entity\Platform\Lazada\LazadaStore;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

class LazadaAPIClient
{
    protected string $lazadaKey, $lazadaSecret;
    protected string $lazadaTestId;
    protected CacheInterface $cache;
    protected Environment $twig;
    private HttpClientInterface $httpClient;
    protected LoggerInterface $logger;

    private LazadaStore $store;

    const HOST = 'https://api.lazada.com.my/rest';

    public function __construct(string $lazadaKey, string $lazadaSecret, CacheInterface $cache, Environment $twig, LoggerInterface $debugLogger, HttpClientInterface $httpClient)
    {
        $this->lazadaKey = $lazadaKey;
        $this->lazadaSecret = $lazadaSecret;

        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->logger = $debugLogger;
        $this->twig = $twig;
    }

    public function setStore(LazadaStore $store)
    {
        $this->store = $store;
    }

    protected function _getAccessToken(): string
    {
        $currentTime = time();
        $access = $this->store->getAccessToken();
        $accessExpires = $this->store->getAccessExpires();
        if (empty($access) || $accessExpires->getTimestamp() <= $currentTime) {
            $refresh = $this->store->getRefreshToken();
            $refreshExpires = $this->store->getRefreshExpires();
            if (empty($refresh) || $refreshExpires->getTimestamp() <= $currentTime)
                throw new \Exception('Lazada refresh token expired. Please remove and re-link the store.');

            $response = $this->post('/auth/token/refresh', ['refresh_token' => $refresh], false);
            if ($response['code'] == 0) {
                $this->store->setAccessToken($response['access_token']);
                $this->store->setAccessExpires(new \DateTime('@' . $currentTime + $response['expires_in']));
                $this->store->setRefreshToken($response['refresh_token']);
                $this->store->setRefreshExpires(new \DateTime('@' . $currentTime + $response['refresh_expires_in']));

                $access = $response['access_token'];
            } else throw new \Exception('Unable to refresh Lazada access token. Please remove and re-link the store.');
        };
        return $access;
    }

    protected function get(string $path, array $params, $requireToken = true): array
    {
        /** Append required validation data/ */
        $params['app_key'] = $this->lazadaKey;
        $params['timestamp'] = explode(' ', microtime())[1] . '000';
        if ($requireToken)
            $params['access_token'] = $this->_getAccessToken($this->store);

        /** Generate signature and append to request parameters */
        $params['sign_method'] = 'sha256';
        ksort($params);
        $paramString = $path;
        foreach ($params as $lazadaKey => $value) {
            if ($paramString === 'batch_id') continue;
            if (is_array($value)) continue;
            $paramString .= $lazadaKey . $value;
        };
        $params['sign'] = strtoupper(hash_hmac('sha256', $paramString, $this->lazadaSecret));
        return $this->request(self::HOST . $path, 'GET', ['query' => $params]);
    }

    protected function post(string $path, array $params, $requireToken = true): array
    {
        /** Append required validation data/ */
        $params['app_key'] = $this->lazadaKey;
        $params['timestamp'] = explode(' ', microtime())[1] . '000';
        if ($requireToken)
            $params['access_token'] = $this->_getAccessToken($this->store);

        /** Generate signature and append to request parameters */
        $params['sign_method'] = 'sha256';
        ksort($params);
        $paramString = $path;
        foreach ($params as $lazadaKey => $value) {
            if ($paramString === 'batch_id') continue;
            $paramString .= $lazadaKey . $value;
        }
        $params['sign'] = strtoupper(hash_hmac('sha256', $paramString, $this->lazadaSecret));

        return $this->request(self::HOST . $path, 'POST', ['body' => $params]);
    }

    private function request(string $url, string $method, array $options): array
    {
        $response = $this->httpClient->request($method, $url, $options);
        return $response->toArray();
    }
}
