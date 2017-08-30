<?php
namespace Soda\Spotify\Api;

class SpotifySession
{
    private $accessToken = '';
    private $clientId = '';
    private $clientSecret = '';
    private $expirationTime = 0;
    private $redirectUri = '';
    private $refreshToken = '';
    private $request = null;

    /**
     * Constructor
     * Set up client credentials.
     *
     * @param string  $clientId     The client ID.
     * @param string  $clientSecret The client secret.
     * @param string  $redirectUri  Optional. The redirect URI.
     * @param Request $request      Optional. The Request object to use.
     */
    public function __construct($clientId, $clientSecret, $redirectUri = '', $request = null)
    {
        $this->setSpotifyId($clientId);
        $this->setSpotifySecret($clientSecret);
        $this->setRedirectUri($redirectUri);

        $this->request = $request ?: new Request();
    }

    /**
     * Get the authorization URL.
     *
     * @param array|object $options Optional. Options for the authorization URL.
     *                              - array scope Optional. Scope(s) to request from the user.
     *                              - boolean show_dialog Optional. Whether or not to force the user to always approve the app. Default is false.
     *                              - string state Optional. A CSRF token.
     *
     * @return string The authorization URL.
     */
    public function getAuthorizeUrl($options = [])
    {
        $options = (array)$options;

        $parameters = [
            'client_id'     => $this->getSpotifyId(),
            'redirect_uri'  => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope'         => isset($options['scope']) ? implode(' ', $options['scope']) : null,
            'show_dialog'   => !empty($options['show_dialog']) ? 'true' : null,
            'state'         => isset($options['state']) ? $options['state'] : null,
        ];

        return Request::ACCOUNT_URL.'/authorize/?'.http_build_query($parameters);
    }

    /**
     * Get the client ID.
     *
     * @return string The client ID.
     */
    public function getSpotifyId()
    {
        return $this->clientId;
    }

    /**
     * Set the client ID.
     *
     * @param string $clientId The client ID.
     *
     * @return void
     */
    public function setSpotifyId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Get the client's redirect URI.
     *
     * @return string The redirect URI.
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set the client's redirect URI.
     *
     * @param string $redirectUri The redirect URI.
     *
     * @return void
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * Get the access token.
     *
     * @return string The access token.
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Get the access token expiration time.
     *
     * @return int A Unix timestamp indicating the token expiration time.
     */
    public function getTokenExpiration()
    {
        return $this->expirationTime;
    }
    public function setTokenExpiration($expirationTime)
    {
        $this->expirationTime = $expirationTime;
    }

    /**
     * Get the refresh token.
     *
     * @return string The refresh token.
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * Refresh an access token.
     *
     * @param string $refreshToken The refresh token to use.
     *
     * @return bool Whether the access token was successfully refreshed.
     */
    public function refreshAccessToken($refreshToken)
    {
        $payload = base64_encode($this->getSpotifyId().':'.$this->getSpotifySecret());

        $parameters = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        $headers = [
            'Authorization' => 'Basic '.$payload,
        ];

        $response = $this->request->account('POST', '/api/token', $parameters, $headers);
        $response = $response['body'];

        if (isset($response->access_token)) {
            $this->accessToken = $response->access_token;
            $this->expirationTime = time() + $response->expires_in;

            return true;
        }

        return false;
    }

    /**
     * Get the client secret.
     *
     * @return string The client secret.
     */
    public function getSpotifySecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set the client secret.
     *
     * @param string $clientSecret The client secret.
     *
     * @return void
     */
    public function setSpotifySecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Request an access token using the Spotify Credentials Flow.
     *
     * @param array $scope Optional. Scope(s) to request from the user.
     *
     * @return bool True when an access token was successfully granted, false otherwise.
     */
    public function requestCredentialsToken($scope = [])
    {
        $payload = base64_encode($this->getSpotifyId().':'.$this->getSpotifySecret());

        $parameters = [
            'grant_type' => 'client_credentials',
            'scope'      => implode(' ', $scope),
        ];

        $headers = [
            'Authorization' => 'Basic '.$payload,
        ];

        $response = $this->request->account('POST', '/api/token', $parameters, $headers);
        $response = $response['body'];

        if (isset($response->access_token)) {
            $this->accessToken = $response->access_token;
            $this->expirationTime = time() + $response->expires_in;

            return true;
        }

        return false;
    }

    /**
     * Request an access token given an authorization code.
     *
     * @param string $authorizationCode The authorization code from Spotify.
     *
     * @return bool True when the access token was successfully granted, false otherwise.
     */
    public function requestAccessToken($authorizationCode)
    {
        $parameters = [
            'client_id'     => $this->getSpotifyId(),
            'client_secret' => $this->getSpotifySecret(),
            'code'          => $authorizationCode,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->getRedirectUri(),
        ];

        $response = $this->request->account('POST', '/api/token', $parameters, []);
        $response = $response['body'];

        if (isset($response->refresh_token) && isset($response->access_token)) {
            $this->refreshToken = $response->refresh_token;
            $this->accessToken = $response->access_token;
            $this->expirationTime = time() + $response->expires_in;

            return true;
        }

        return false;
    }
}
