<?php

    namespace Soda\Spotify\Api;

    use Exception;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Redirect;
    use Soda\Spotify\Components\Helpers;

    class SearchInterface extends Controller {
        /**
         * @param $searchterm
         * @param string $type -- spotify media to return. track, artist or album
         * @param bool $fuzzy -- use fuzzy search on either end of the searchterm
         * @param int $limit - max results amount
         *
         * @return mixed Search for a given searchterm
         *
         * Search for a given searchterm
         */
        public static function search($searchterm, $type = 'track', $fuzzy = true, $limit = 5) {
            try {
                $api = APIInterface::initPublicAPI();

                if ($fuzzy) $searchterm = '*' . $searchterm . '*';

                $tracks = $api->search($searchterm, $type,
                [
                    'limit' => $limit,
                ]);

                $tracks = Helpers::removeExplicit($tracks->tracks->items);

                return $tracks;

            } catch (Exception $ex) {
                // Expired, need to log in again
                return Redirect::to(route('spotify.logout'));
            }
        }
    }
