<?php namespace CSApi;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

class OAuthAuthorization
{
    /**
     * Calls access token provider and stores it in sessions
     *
     * @param array $config
     * @param mixed $accessToken
     *
     * @return array
     */
    public static function callAccessTokenProvider(array $config, $accessToken = false)
    {
        $provider = new GenericProvider([
            'clientId'                => $config['clientId'],
            'clientSecret'            => $config['clientSecret'],
            'redirectUri'             => $config['redirectUri'],
            'urlAuthorize'            => $config['urlAuthorize'],
            'urlAccessToken'          => $config['urlAccessToken'],
            'urlResourceOwnerDetails' => $config['urlResourceOwnerDetails'],
        ]);

        if (
            isset($accessToken['access_token']) &&
            isset($accessToken['refresh_token']) &&
            isset($accessToken['token_type']) &&
            isset($accessToken['expires']) &&
            $accessToken['expires'] < time()
        ){
            $newAccessToken = self::refreshAccessToken($provider, $accessToken['refresh_token']);

            $accessToken['access_token'] = $newAccessToken->getToken();
            $accessToken['expires'] = $newAccessToken->getExpires();
        }
        else if(
            !isset($accessToken['access_token']) ||
            !isset($accessToken['token_type']) ||
            !isset($accessToken['expires']) ||
            (isset($accessToken['expires']) && $accessToken['expires'] < time())
        ){
            $newAccessToken = self::requestNewAccessToken($provider);

            $accessToken['access_token'] = $newAccessToken->getToken();
            $accessToken['refresh_token'] = $newAccessToken->getRefreshToken();
            $accessToken['expires'] = $newAccessToken->getExpires();
            $accessToken['token_type'] = $newAccessToken->getValues()['token_type'];
        }

        return $accessToken;
    }

    /**
     * Calls access token provider and returns new access token parameters
     *
     * @param GenericProvider $provider
     *
     * @return AccessToken
     */

    public static function requestNewAccessToken(GenericProvider $provider)
    {
        // If we don't have an authorization code then get one
        if (!isset($_GET['code'])) {

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $options = [
                'state'                   => 'profil',
                'response_type'           => 'code',
                'access_type'             => 'offline',
                'approval_prompt'         => 'force'
            ];
            $authorizationUrl = $provider->getAuthorizationUrl($options);

            // Redirect the user to the authorization URL.
            header('Location: ' . $authorizationUrl);
            exit;


        }
        else {

            try {

                // Try to get an access token using the authorization code grant.
                $newAccessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                return $newAccessToken;

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                // Failed to get the access token or user details.
                exit($e->getMessage());

            }

        }
    }

    /**
     * Calls access token provider and returns refreshed access token parameters
     *
     * @param GenericProvider $provider
     * @param $refreshToken
     *
     * return AccessToken
     */

    public static function refreshAccessToken(GenericProvider $provider, $refreshToken)
    {
        $newAccessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken,
        ]);

        return $newAccessToken;
    }
}