<?php

    namespace Soda\Spotify\Api;

    use App\Http\Controllers\Controller;
    use Soda\Spotify\Components\Helpers;

    // https://developer.spotify.com/web-api/web-api-connect-endpoint-reference/
    class PlayerInterface extends Controller {

        public static $scopes = [
            'user-read-playback-state',
            'user-modify-playback-state',
        ];

        // https://developer.spotify.com/web-api/get-a-users-available-devices/
        public static function get_devices($user=false){
            $user = $user ? $user : AuthInterface::get_user();
            if( $user ) {
                $api = APIInterface::getUserAPI($user);

                $devices = $api->myDevices();
                if( $devices && isset($devices->devices) ){
                    $devices = $devices->devices;

                    return $devices;
                }
            }
            return [];
        }

        // return the first device listed as available
        public static function get_first_available_device(){
            $devices = PlayerInterface::get_devices();
            if( count($devices) ){
                $available_device = array_filter(
                    $devices,
                    function ($device) {
                        return $device->is_active;
                    }
                );

                return count($available_device) > 0 ? array_first($available_device) : false;
            }
            return false;
        }

        public static function play_on_device($content, $device_id, $user = false) {
            $user = $user ? $user : AuthInterface::get_user();
            if ( $user && Helpers::hasScopes(PlayerInterface::$scopes) ) {
                $api = APIInterface::getUserAPI($user);

                $options = [];
                if( is_array($content) || strpos($content, 'track') !== false ){
                    $options['uris'] = is_array($content) ? $content : [$content];
                }
                else{
                    $options['context_uri'] = $content;
                }

                return $api->devicePlay($options);
            }

            return false;
        }
    }
