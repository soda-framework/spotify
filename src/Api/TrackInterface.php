<?php

    namespace Soda\Spotify\Api;

    use Exception;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Redirect;
    use Soda\Spotify\Components\Helpers;

    class TrackInterface extends Controller {
        /**
         * @param $id
         *
         * @return array|object
         *
         * Get the track object for an id
         */
        public static function get_track($id) {
            $api = APIInterface::initPublicAPI();

            return $api->getTrack($id);
        }

        public static function get_tracks($ids) {
            $api = APIInterface::initPublicAPI();

            return $api->getTracks($ids);
        }

        public static function get_track_features($id) {
            $api = APIInterface::initPublicAPI();

            return $api->getAudioFeatures([$id]);
        }

        public static function get_tracks_features($ids) {
            $api = APIInterface::initPublicAPI();

            return $api->getAudioFeatures($ids);
        }

        public static function get_user_top($type= 'tracks',$limit = 20) {
            try {
                $api = APIInterface::getAPI();

                $top_tracks = $api->getMyTop($type, ['limit' => $limit]);

                return $top_tracks;

            } catch (Exception $ex) {
                // Expired, need to log in again
                return $ex->getMessage();
            }
        }

        public static function get_user_recently_played($limit = 20, $after=false, $before=false) {
            try {
                $api = APIInterface::getAPI();

                $options = ['limit' => $limit];
                if( $after ) $options['after'] = $after;
                if( $before ) $options['before'] = $before;

                $recent_tracks = $api->getRecentlyPlayed($options);

                return $recent_tracks;
            } catch (Exception $ex) {
                // Expired, need to log in again
                return $ex->getMessage();
            }
        }

        /**
         * @param $uri
         *
         * @return bool
         *
         * Get the ID from a track uri in the format ( spotify:track:6MUqvjHsxLPSfqFRaww9KZ )
         */
        public static function get_id_from_uri($uri) {
            $bits = explode(':', $uri);
            if ( count($bits) >= 3 ) {
                return trim($bits[2]);
            }
            return false;
        }

        /**
         * @param $url
         *
         * @return bool
         *
         * Get the ID from a track url in the format ( https://open.spotify.com/track/5SFn7vpBSiLUBn1mDct8c9 )
         */
        public static function get_id_from_url($url) {
            $url = parse_url($url, PHP_URL_PATH);

            $bits = explode('/', $url);
            if ( count($bits) >= 0 ) {
                return trim(last($bits));
            }
            return false;
        }
    }
