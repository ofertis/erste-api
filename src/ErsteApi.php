<?php namespace CSApi;

abstract class CSApi
{
    /**
     * Configuration settings
     *
     * @var array
     */
    protected $config;

    /**
     * Access token parameters for authentication
     *
     * @var array
     */
    protected $accessToken;

    /**
     * Initializes CSApi and starts authentication process
     *
     * @param array $config
     * @param mixed $accessToken
     */
    public function __construct(array $config, $accessToken = false)
    {
        $this->config = $config;
        $this->accessToken = $accessToken;
        $this->init();
    }

    /**
     * @return array
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Initialize function - get access token parameters from provider
     *
     * @return void
     */
    protected function init()
    {
        $this->accessToken = OAuthAuthorization::callAccessTokenProvider($this->config, $this->accessToken);
    }

    /**
     * Sends a request to API server
     *
     * @param string $apiUrl
     * @param array $urlParameters
     * @param array $postData
     *
     * @return string
     */
    protected abstract function apiRequest($apiUrl, array $urlParameters = [], array $postData = null);
}