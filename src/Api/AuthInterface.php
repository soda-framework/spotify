<?php

    namespace Soda\Spotify\Api;

    use Exception;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\URL;
    use Soda\Spotify\Api\Request;
//    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Input;
    use Illuminate\Support\Facades\Redirect;
    use Illuminate\Support\Facades\Session;
    use Soda\Spotify\Components\Helpers;
    use Soda\Spotify\Controllers\SettingsController;
    use Soda\Spotify\Controllers\TokenController;
    use Soda\Spotify\Models\User;
    use stdClass;

    class AuthInterface extends Controller {
        /**
         * @return boolean
         *
         * Check if user is logged in
         */
        public static function is_logged_in() {
            return Auth::guard('soda-spotify')->check();
        }

        public static function get_user(){
            if( AuthInterface::is_logged_in() ){
                return Auth::guard('soda-spotify')->user();
            }
            return false;
        }

        public static function get_logged_in_user_name() {
            $user = AuthInterface::get_user();
            if ( $user ) {
                return $user->display_name ? $user->display_name : $user->user_id;
            }
            return '';
        }

        public function login($return_url = null) {
            $return_url = $return_url ? $return_url : Input::get('url');
            $return_url = $return_url ? $return_url : URL::previous();

            Helpers::savePage($return_url); // remember where we came from, so we can go back after logging in

            return Redirect::to(route('spotify.login.go')); // need to redirect to save the session set above
        }

        /**
         * Takes the user to spotify to perform the login.
         * User grants permission(s) to the app and it returned
         */
        public function login_go() {
            // forget logged in user
            Helpers::clearSession();

            SpotifyInterface::setSpotifyCredentials(); // set Spotify ID and Secret

            $session = new SpotifySession(SpotifyInterface::$clientID, SpotifyInterface::$clientSecret, route('spotify.login.return'));

            $scopes = config('soda.spotify.login_scopes');

            $authorizeUrl = $session->getAuthorizeUrl([
                'scope' => $scopes,
            ]);

            header('Location: ' . $authorizeUrl);
            die();
        }

        /**
         * @param \Soda\Spotify\Api\Request $request
         *
         * @return mixed Return url when access granted by the user
         *
         * Return url when access granted by the user
         */
        public function login_return(\Illuminate\Http\Request $request) {
            try {
                $token = $request->input('code');
                $api = APIInterface::getAPI();

                $session = new SpotifySession(SpotifyInterface::$clientID, SpotifyInterface::$clientSecret, route('spotify.login.return'));
                $session->requestAccessToken($token);

                $accessToken = $session->getAccessToken();
                $api->setAccessToken($accessToken);
                $api_user = $api->me();

                $user = User::where('user_id',$api_user->id)->count() > 0 ? User::where('user_id', $api_user->id)->first() : new User();

                $user->user_id                 = $api_user->id;
                $user->display_name            = $api_user->display_name;
                $user->email                   = $api_user->email;
                $user->url                     = $api_user->external_urls->spotify;
                $user->uri                     = $api_user->uri;
                if( count($api_user->images) > 0 ) {
                    $user->image        = $api_user->images[0]->url;
                    $user->image_width  = $api_user->images[0]->width;
                    $user->image_height = $api_user->images[0]->height;
                }
                $user->follower_count          = $api_user->followers->total;
                $user->country                 = $api_user->country;
                $user->access_token            = $session->getAccessToken();
                $user->access_token_expiration = $session->getTokenExpiration();
                $user->refresh_token           = $session->getRefreshToken();
                $user->save();

                Auth::guard('soda-spotify')->login($user);

                $return_url = Helpers::merge_get_url(Helpers::getPage(), 'from_spotify=true');

                return Redirect::to($return_url); // go back to where we were when we logged in

            } catch (Exception $ex) {
                // Expired, need to log in again
                return Redirect::to(route('spotify.logout'));
            }
        }

        public static function isTokenExpired($expiration) {
            return time() >= $expiration;
        }

        // check that a response is from spotify
        public static function isSpotifyReturn() {
            return Input::has('from_spotify');
        }

        /**
         * @return mixed
         *
         * Logs out user
         */
        public function logout() {
            Helpers::clearSession();

            $returnUrl = Helpers::merge_get_url(URL::to('/'), \Request::getQueryString());

            return Redirect::to($returnUrl);
        }
    }
