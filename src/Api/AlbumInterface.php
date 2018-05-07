<?php

    namespace Soda\Spotify\Api;

    use App\Http\Controllers\Controller;
    use Soda\Spotify\Components\Helpers;

    class AlbumInterface extends Controller {

        /**
         * @param $id
         *
         * @return array|object
         *
         * Get the artist object for an id
         */
        public static function get_album($id, $api = null) {
            $api = $api ? $api : APIInterface::initPublicAPI();

            return $api->getAlbum($id);
        }

        public static function get_albums($ids, $api = null) {
            $api = $api ? $api : APIInterface::initPublicAPI();

            return $api->getAlbums($ids);
        }

        public static function get_album_url_from_uri($uri) {
            $bits = explode(':', $uri);

            $url = '';
            if ( count($bits) == 3 ) {
                $url = 'https://open.spotify.com/album/' . $bits[2];
            }

            return $url;
        }

        public static function get_album_tracks($albumId, $count = 10, $options = []) {
            $api = APIInterface::initPublicAPI();

            if( !isset($options['limit']) ) $options['limit'] = $count;

            // get top tracks array
            $tracks = $api->getAlbumTracks($albumId, $options);

            $tracks = Helpers::removeExplicit($tracks->tracks);

            // return amount
            $tracks = array_slice($tracks, 0, $count);

            return $tracks;
        }
    }
