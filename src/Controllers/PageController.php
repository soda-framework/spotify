<?php
    namespace Soda\Spotify\Controllers;

    use Illuminate\Support\Facades\Redirect;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Facades\View;
    use Soda\Spotify\Api\AuthInterface;
    use Soda\Spotify\Api\PlaylistInterface;
    use Soda\Spotify\Components\Helpers;
    use Soda\Spotify\Models\Playlist;
    use Soda\Spotify\Models\Settings;
    use Soda\Cms\Http\Controllers\BaseController;
    use Soda\Cms\Models\Page;
    use Soda;
    use Symfony\Component\Debug\Exception;

    class PageController extends BaseController {

        public function playlist($id){
            $playlist = Playlist::find($id);
            $settings = Settings::find(Settings::$settingID);

            if( !$playlist || !$settings ){
                return Redirect::to('/');
            }

            // Add playlist to spotify account, only if not added
            // Spotify login returns here, if 'from_spotify' and not already added, then add.
            if ( AuthInterface::isSpotifyReturn() && ! PlaylistInterface::playlist_added($playlist->id) ) {
                // Add to account
                $params = [
                    'name'        => $settings->playlist_title,
                    'tracklist'   => Helpers::getIDs(json_decode($playlist->tracks)), // gets ID's from an array of spotify track objects
                    'playlist_id' => $playlist->id,
                ];
                if( $settings->playlist_image ){
                    $params['image'] = $settings->playlist_image;
                }

//                Session::save();

                $response = Redirect::to(route('spotify.api.playlist.add') . '?' . http_build_query($params));

                return $response;
            }

            $settings->package = $settings->package ? $settings->package . '::' : $settings->package; // when no package is defined
            return View::make($settings->package . $settings->action, compact('playlist'));
        }
    }
