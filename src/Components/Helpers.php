<?php

    namespace Soda\Spotify\Components;

    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Session;

    class Helpers
    {
        /**
         * @param $url
         * @param $other_query_string
         *
         * @return string URL
         *
         * Combine query strings
         * http://stackoverflow.com/a/3002497
         *
         * e.g.
         * merge_get_url('http://google.com','a=true') => http://google.com?a=true
         * merge_get_url('http://google.com?b=false','a=true') => http://google.com?b=false&a=true
         */
        public static function merge_get_url($url, $other_query_string) {
            // Parse the URL into components
            $url_parsed = parse_url($url);
            $new_qs_parsed = [];
            // Grab our first query string
            if( isset($url_parsed['query']) ) {
                parse_str($url_parsed['query'], $new_qs_parsed);
            }

            // Here's the other query string
            $other_qs_parsed = [];
            parse_str($other_query_string, $other_qs_parsed);
            // Stitch the two query strings together
            $final_query_string_array = array_merge($new_qs_parsed, $other_qs_parsed);
            $final_query_string = http_build_query($final_query_string_array);
            // Now, our final URL:
            $new_url = $url_parsed['scheme']
                . '://'
                . $url_parsed['host']
                . @$url_parsed['path']
                . '?'
                . $final_query_string;

            return $new_url;
        }

        /**
         * @param $page
         *
         * remember where we came from when logging in, so we can go back after coming back once logged in
         */
        public static function savePage($url) {
            Session::put('currentPage', $url);
        }

        /**
         * @return mixed
         *
         * get where we came from when logging in
         */
        public static function getPage() {
            return Session::get('currentPage');
        }

        /**
         * Wipe spotify session data
         */
        public static function clearSession() {
            Auth::guard('soda-spotify')->logout();
        }

        // get just the ID's
        public static function getIDs($items) {
            return (object) array_map(function ($item) {
                return $item->id;
            }, (array) $items);
        }

        /**
         * Removes tracks that are explicit IF the config is set to remove them
         *
         * @param $tracks - array|object of spotify tracks
         *
         * @return array|object $tracks
         */
        public static function removeExplicit($tracks) {
            // remove explicit tracks
            if ( config('soda.spotify.allow_explicit') === true ) {
                // create new empty container
                $appropriate_tracks = [];

                // check each track
                foreach ($tracks as $track) {

                    // be respective of objects or arrays
                    if ( (is_object($track) && ! $track->explicit) || (is_array($track) && ! $track['explicit']) ) {

                        // add
                        $appropriate_tracks[] = $track;
                    }
                }

                if ( is_object($tracks) ) return (object) $tracks;

                // reassign
                $tracks = is_object($tracks) ? (object) $appropriate_tracks : $appropriate_tracks;
            }

            return $tracks;
        }

        public static function checkDuplicateTracks($tracks, $track) {
            foreach ($tracks as $item) {
                if ( $item->id == $track->id ) {
                    return true;
                }
            }

            return false;
        }

        public static function checkDuplicateArtists($tracks, $track) {
            foreach ($tracks as $item) {
                foreach ($item->artists as $artist) {
                    foreach ($track->artists as $track_artist) {
                        if ( $artist->id == $track_artist->id ) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        /**
         * @param $items
         * @param bool $limit
         *
         * @return array
         *
         * Remove duplicate artists
         */
        public static function removeDuplicateArtists($items, $limit=false) {
            $results = []; // unique results
            $found_artists = []; // database of processed keys

            foreach ($items as $item) {
                // stop if already reached limit
                if ( $limit && count($results) >= $limit ) break;

                // if not already found
                if ( ! in_array($item->artists[0]->id, $found_artists) ) {
                    // add to found artists
                    $found_artists[] = $item->artists[0]->id;
                    // add item to unique results
                    $results[] = $item;
                }
            }

            // ensure limit met
            if ( $limit ) {
                $results = array_slice($results, 0, $limit);
            }

            return $results;
        }

        /**
         * @param $items
         * @param $limit
         *
         * @return array|object
         *
         * Remove duplicate tracks
         */
        public static function removeDuplicateTracks($items, $limit=false) {
            $results = []; // unique results
            $found_tracks = []; // database of processed keys

            foreach ($items as $item) {
                // stop if already reached limit
                if ( $limit && count($results) >= $limit ) break;

                // if not already found
                if ( ! in_array($item->id, $found_tracks) ) {
                    // add to found artists
                    $found_tracks[] = $item->id;
                    // add item to unique results
                    $results[] = $item;
                }
            }

            // ensure limit met
            if( $limit ){
                $results = array_slice($results, 0, $limit);
            }

            return $results;
        }

        public static function reduceResults($tracks) {
            foreach ($tracks as $key => $track) {
                $json = [
                    'id'          => $track->id,
                    'name'        => $track->name,
                    'preview_url' => $track->preview_url,
                    'uri'         => $track->uri,
                    'url'         => $track->external_urls->spotify,
                    'image'       => isset($track->album) &&
                                     isset($track->album->images) &&
                                     count($track->album->images) > 0 ?
                                        $track->album->images[0]->url : null,
                    'artists'     => self::json_artists($track->artists)
                ];

                $tracks[$key] = $json;
            }

            return $tracks;
        }

        public static function json_artists($artists) {
            $json = [];
            foreach ($artists as $artist) {
                $json[] = [
                    'id'   => $artist->id,
                    'name' => $artist->name,
                    'uri'  => $artist->uri,
                    'url'  => $artist->external_urls->spotify,
                ];
            }

            return $json;
        }

        public static function close_tab(){
            return '<script>window.close()</script>';
        }
    }
