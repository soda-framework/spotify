<?php

    namespace Soda\Spotify\Api;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Session;
    use Soda\Spotify\Controllers\TokenController;
    use Soda\Spotify\Models\User;

    class APIInterface extends Controller {
        public static $api = null;

        /**
         * Supply the correctly authenticated API based on what's available
         **/
        public static function getAPI() {
            // If the ClientToken has been set
            if ( TokenController::accessToken() && TokenController::refreshToken() ) {
                return APIInterface::getClientAPI();
            }
            else if ( AuthInterface::is_logged_in() ) {
                return APIInterface::getUserAPI( AuthInterface::get_user() );
            }
            else {
                return APIInterface::getPublicAPI();
            }
        }

        /**
         * @return API
         */
        public static function getClientAPI() {
            SpotifyInterface::setSpotifyCredentials();

            $api = new Spotify();
            $session = new SpotifySession(SpotifyInterface::$clientID, SpotifyInterface::$clientSecret, route('spotify.login.return'));

            $accessToken = TokenController::accessToken();
            $expirationTime = TokenController::accessTokenExpiration();
            $refreshToken = TokenController::refreshToken();
            $session->setAccessToken($accessToken);
            $session->setTokenExpiration($expirationTime);
            $session->setRefreshToken($refreshToken);

            // check if need to refresh
            if ( TokenController::isTokenExpired() ) {
                $session->refreshAccessToken($refreshToken);
                $accessToken = $session->getAccessToken();
                $expirationTime = $session->getTokenExpiration();
                TokenController::updateTokens($accessToken, $expirationTime);
            }

            // Set the code on the API wrapper
            $api->setAccessToken($accessToken);

            return $api;
        }

        /**
         * @param $user
         *
         * @return API
         */
        public static function getUserAPI($user) {
            SpotifyInterface::setSpotifyCredentials();

            $api = new Spotify();
            $session = new SpotifySession(SpotifyInterface::$clientID, SpotifyInterface::$clientSecret, route('spotify.login.return'));

            $session->setAccessToken($user->access_token);
            $session->setTokenExpiration($user->access_token_expiration);
            $session->setRefreshToken($user->refresh_token);

            // check if need to refresh
            if ( AuthInterface::isTokenExpired($user->access_token_expiration) ) {
                $session->refreshAccessToken($user->refresh_token);
                $user->access_token = $session->getAccessToken();
                $user->access_token_expiration = $session->getTokenExpiration();
                $user->save();
            }

            // Set the code on the API wrapper
            $api->setAccessToken($user->access_token);

            return $api;
        }

        /**
         * @return API
         */
        public static function getPublicAPI() {
            SpotifyInterface::setSpotifyCredentials();

            $api = new Spotify();
            $session = new SpotifySession(SpotifyInterface::$clientID, SpotifyInterface::$clientSecret, route('spotify.login.return'));

            // Request a access token with optional scopes
            $scopes = [];
            $session->requestCredentialsToken($scopes);

            $accessToken = $session->getAccessToken(); // We're good to go!

            // Set the code on the API wrapper
            $api->setAccessToken($accessToken);

            return $api;
        }

        public static function initPublicAPI($api = null) {
            if ( @$api ) return $api;
            if ( APIInterface::$api ) return APIInterface::$api;

            return APIInterface::$api = APIInterface::getPublicAPI();
        }

        public static function initAPI($api = null) {
            if (@$api) return $api;
            if (APIInterface::$api) return APIInterface::$api;
            return APIInterface::$api = APIInterface::getAPI();
        }
    }
