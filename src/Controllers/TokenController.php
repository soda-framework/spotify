<?php
    namespace Soda\Spotify\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Input;
    use Illuminate\Support\Facades\Redirect;
    use Soda\Spotify\Api\Spotify;
    use Soda\Spotify\Api\SpotifyInterface;
    use Soda\Spotify\Api\SpotifySession;
    use Soda\Spotify\Components\Helpers;
    use Soda\Spotify\Models\Settings;
    use Soda\Cms\Http\Controllers\BaseController;
    use Soda;
    use Symfony\Component\Debug\Exception;

    class TokenController extends BaseController {

        public static function accessToken() {
            $settings = Settings::find(Settings::$settingID);

            return $settings ? $settings->access_token : null;
        }

        public static function accessTokenExpiration() {
            $settings = Settings::find(Settings::$settingID);

            return $settings ? (int) $settings->access_token_expiration : null;
        }

        public static function refreshToken() {
            $settings = Settings::find(Settings::$settingID);

            return $settings ? $settings->refresh_token : null;
        }

        public static function isTokenExpired() {
            return time() >= self::accessTokenExpiration();
        }

        public static function updateTokens($access_token, $access_token_expiration) {
            $settings = Settings::find(Settings::$settingID);
            if ( $settings ) {
                $settings->access_token = $access_token;
                $settings->access_token_expiration = $access_token_expiration;
                $settings->save();
            }
        }

        public function login($return_url = null) {
            $return_url = $return_url ? $return_url : Input::get('url');
            $return_url = $return_url ? $return_url : URL::previous();

            Helpers::savePage($return_url); // remember where we came from, so we can go back after logging in

            return Redirect::to(route('spotify.token.login.go')); // need to redirect to save the session set above
        }

        /**
         * Takes the user to spotify to perform the login.
         * User grants permission(s) to the app and it returned
         */
        public function login_go() {
            // forget logged in user
            Helpers::clearSession();

            SpotifyInterface::setSpotifyCredentials(); // set Spotify ID and Secret

            $session = new SpotifySession(SpotifyInterface::$clientID, SpotifyInterface::$clientSecret, route('spotify.token.login.return'));

            $scopes = config('soda.spotify.login_scopes');

            $authorizeUrl = $session->getAuthorizeUrl([
                'scope' => $scopes,
            ]);

            header('Location: ' . $authorizeUrl);
            die();
        }

        /**
         * @param Request $request
         *
         * @return mixed Return url when access granted by the user
         *
         * Return url when access granted by the user
         */
        public function login_return(Request $request) {

            $token = $request->input('code');

            $api = new Spotify();
            SpotifyInterface::setSpotifyCredentials();

            $session = new SpotifySession(SpotifyInterface::$clientID, SpotifyInterface::$clientSecret, route('spotify.token.login.return'));

            $session->requestAccessToken($token);

            $accessToken = $session->getAccessToken();
            $api->setAccessToken($accessToken);
            $user = $api->me();

            $settings = Settings::find(Settings::$settingID);
            $settings->token_user = $user->id;
            $settings->token_user_link = $user->external_urls->spotify;
            $settings->access_token = $session->getAccessToken();
            $settings->access_token_expiration = $session->getTokenExpiration();
            $settings->refresh_token = $session->getRefreshToken();
            $settings->save();

            return Redirect::to(route('spotify.settings'));
        }
    }
