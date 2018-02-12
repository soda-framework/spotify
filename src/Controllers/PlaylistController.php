<?php
    namespace Soda\Spotify\Controllers;

    use Soda\Spotify\Api\BrowseInterface;
    use Soda\Spotify\Api\PlaylistInterface;
    use Soda\Spotify\Api\TrackInterface;
    use Soda\Spotify\Components\Helpers;
    use Soda;
    use Soda\Cms\Http\Controllers\BaseController;
    use Soda\Spotify\Api\SpotifyInterface;
    use Soda\Spotify\Models\Playlist;
    use Symfony\Component\Debug\Exception;
    use Zofe\Rapyd\DataFilter\DataFilter;
    use Zofe\Rapyd\DataGrid\DataGrid;

    class PlaylistController extends BaseController {

        public function anyIndex() {
            $filter = DataFilter::source(new Playlist());
            $filter->add('tracks', 'Tracks', 'text');
            $filter->submit('Search');
            $filter->reset('Clear');
            $filter->build();
            $grid = DataGrid::source($filter);
            $grid->add('id', 'ID', true);
            $grid->add('tracks', 'Tracks', true);
            $grid->add('created_at|strtotime|date[d/m/Y]', 'Created At', true);
            $grid->add('{{ $id }}', 'Options')->cell(function ($id) {
                return '<a href=" ' . route('spotify.playlists.get.playlist', $id) . '" class="btn btn-warning">View</a>';
            });
            $grid->paginate(20);

            return view('soda-spotify::cms.playlists.index', compact('filter', 'grid'));
        }

        public function getPlaylist($id = null) {
            $playlist = null;
            if ( ! is_null($id) ) {
                $playlist = Playlist::find($id);
            }

            return view('soda-spotify::cms.playlists.playlist', compact('playlist'));
        }

        public static function createPlaylistFromURI($uri, $length = 30) {
            $uri = explode(':', $uri);
            $spotify_playlist = PlaylistInterface::get_playlist($uri[2], $uri[4]);
            $tracks = PlaylistInterface::get_playlist_tracks($spotify_playlist);
            $tracks = array_slice($tracks, 0, $length);
            $tracks = Helpers::reduceResults($tracks);

            $playlist = new Playlist();
            $playlist->tracks = json_encode($tracks);
            $playlist->uri = $spotify_playlist->uri;
            $playlist->url = $spotify_playlist->external_urls->spotify;
            $playlist->save();

            return $playlist;
        }

        public function tracks($id) {
            $playlist = Playlist::whereId($id)->first();
            if ( ! $playlist ) {
                return response()->json(['success' => false, 'redirect' => '/']);
            }

            return response()->json(['success' => true, 'tracks' => json_decode($playlist->tracks)]);
        }

        /**
         * @param $seeds - array of seeds as defined in https://developer.spotify.com/web-api/get-recommendations/
         * @param int $limit (optional) - desired number of tracks
         *
         * @param bool $merge_seeds - whether or not to add the seed track into the results
         * @param array $filler_track_ids
         *
         * @return Playlist
         * @throws \Exception
         */
        public static function create_seeded_playlist($seeds, $limit = 20, $filler_track_ids = [], $merge_seeds = true) {
            if ( isset($seeds['seed_genres']) || isset($seeds['seed_tracks']) || isset($seeds['seed_artists']) ) {

                // lots of songs don't make the cut, overestimate the limit and trim later
                $seed_limit = $limit < 100 ? 100 : $limit * 2;

                // apply parameters
                $seeds['limit'] = $seed_limit;

                // get tracks
                $tracks = BrowseInterface::getRecommendations($seeds);
                $tracks = $tracks->tracks;

                // merge seed tracks
                if ( $merge_seeds && isset($seeds['seed_tracks']) ) {
                    $seed_tracks = TrackInterface::get_tracks($seeds['seed_tracks']);
                    $seed_tracks = $seed_tracks->tracks;
                    $tracks = array_merge($seed_tracks, $tracks);
                }

                $tracks = Helpers::removeExplicit($tracks); // remove explicit

                $tracks = Helpers::removeDuplicateArtists($tracks); // remove duplicates

                if ( count($filler_track_ids) > 0 ) {
                    $tracks = Helpers::reachTrackLimit($tracks, $limit, $filler_track_ids); // remove duplicates
                }

                $tracks = Helpers::reduceResults($tracks); // convert tracks to smaller data

                // trim to actual limit
                $tracks = array_slice($tracks, 0, $limit);

                // create playlist
                $playlist = new Playlist();
                $playlist->tracks = json_encode($tracks);
                $playlist->save();

                return $playlist;
            } else {
                throw new \Exception("You MUST have any one of seed_genres, seed_tracks or seed_artists.");
            }
        }
    }
