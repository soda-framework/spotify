<?php

    namespace Soda\Spotify\Api;

    use App\Http\Controllers\Controller;
    use Soda\Spotify\Components\Helpers;

    class ArtistInterface extends Controller {

        /**
         * @param $id
         *
         * @return array|object
         *
         * Get the artist object for an id
         */
        public static function get_artist($id, $api = null) {
            $api = $api ? $api : APIInterface::initPublicAPI();

            return $api->getArtist($id);
        }

        public static function get_artists($ids, $api = null) {
            $api = $api ? $api : APIInterface::initPublicAPI();

            return $api->getArtists($ids);
        }

        public static function get_artist_image($artist) {
            $image = @$artist->images;
            $image = reset($image);
            $image = @$image->url;

            return $image;
        }

        public static function get_artist_name($artist) {
            return @$artist->name;
        }

        public static function get_artist_url_from_uri($uri) {
            $bits = explode(':', $uri);

            $url = '';
            if ( count($bits) == 3 ) {
                $url = 'https://open.spotify.com/artist/' . $bits[2];
            }

            return $url;
        }

        /**
         * @param $artist - artist ID
         *
         * @return mixed
         *
         * Get related artists from an artists object
         */
        public static function get_related_artists($artist, $count = 10) {
            $api = APIInterface::initPublicAPI();

            // get related artists array
            $artists = $api->getArtistRelatedArtists($artist);

            // return amount
            $artists = array_slice($artists->artists, 0, $count);

            return $artists;
        }

        public static function get_artist_top_tracks($artist, $count = 10, $country = 'US') {
            $api = APIInterface::initPublicAPI();

            // get top tracks array
            $tracks = $api->getArtistTopTracks($artist, ['country' => $country]);

            $tracks = Helpers::removeExplicit($tracks->tracks);

            // return amount
            $tracks = array_slice($tracks, 0, $count);

            return $tracks;
        }
    }
