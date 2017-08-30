<?php

    namespace Soda\Spotify\Api;

    use Exception;
//    use Soda\Spotify\Api\Request;
    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Input;
    use Illuminate\Support\Facades\Redirect;
    use Illuminate\Support\Facades\Session;
    use Soda\Spotify\Controllers\SettingsController;
    use Soda\Spotify\Controllers\TokenController;

    class SpotifyInterface extends Controller {
        public static $clientID;
        public static $clientSecret;

        // return type associations
        public static $ARRAY = true;
        public static $OBJECT = false;

        public function __construct() {
            SpotifyInterface::setSpotifyCredentials();
            APIInterface::$api = APIInterface::getAPI();
        }

        public static function setSpotifyCredentials() {
            static::$clientID = SettingsController::clientId();//env('SPOTIFY_CLIENT_ID');
            static::$clientSecret = SettingsController::clientSecret();//env('SPOTIFY_CLIENT_SECRET');
        }
    }
