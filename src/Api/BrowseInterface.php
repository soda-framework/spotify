<?php

    namespace Soda\Spotify\Api;

    use App\Http\Controllers\Controller;

    class BrowseInterface extends Controller {
        /**
         * @param $options
         *
         * @return mixed
         *
         * Get recommendations based on seeds
         */
        public static function getRecommendations($options) {
            $api = APIInterface::getPublicAPI();

            $recommendations = $api->getRecommendations($options);

            return $recommendations;
        }

        /**
         * @return array|object
         *
         * Get Available Genre Seeds
         */
        public static function getGenreSeeds() {
            $api = APIInterface::getPublicAPI();

            $genres = $api->getGenreSeeds();

            return $genres;
        }
    }
