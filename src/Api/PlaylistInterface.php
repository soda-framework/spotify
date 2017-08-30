<?php

    namespace Soda\Spotify\Api;

    use Exception;
    use Illuminate\Http\Request;
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Redirect;
    use Illuminate\Support\Facades\Session;
    use Soda\Spotify\Components\Helpers;
    use Soda\Spotify\Models\Playlist;
    use Soda\Spotify\Models\Settings;

    class PlaylistInterface extends Controller {
        public static function get_playlist($user_id, $id, $api = null) {
            $api = APIInterface::initPublicAPI();

            $playlist = $api->getUserPlaylist($user_id, $id);

            return $playlist;
        }

        public static function get_playlists($user_id, $api = null) {
            $api = APIInterface::initPublicAPI();

            $playlist = $api->getUserPlaylists($user_id);

            return $playlist;
        }

        public static function get_user_playlist($user, $id, $api = null) {
            $api = APIInterface::getUserAPI($user);

            $playlist = $api->getUserPlaylist($user->user_id, $id);

            return $playlist;
        }

        public static function get_user_playlists($user, $api = null) {
            $api = APIInterface::getUserAPI($user);

            $playlists = $api->getUserPlaylists($user->user_id);

            return $playlists;
        }

        public static function get_playlist_image($playlist) {
            $image = @$playlist->images;
            $image = reset($image);
            $image = @$image->url;

            return $image;
        }

        public static function get_playlist_tracks($playlist) {
            return Helpers::removeExplicit($playlist->tracks->items);
        }

        public static function get_playlist_name($playlist){
            return @$playlist->name;
        }

        public static function get_playlist_url_from_uri($uri) {
            $bits = explode(':', $uri);

            $url = '';
            if (count($bits) == 5) {
                $url = 'https://open.spotify.com/user/' . $bits[2] . '/playlist/' . $bits[4];
            }

            return $url;
        }

        public static function playlist_added($id){
            return Session::has('added_playlist_' . $id);
        }

        public static function follow_link($playlist, $return='redirect'){
            $params = ['playlist_uri' => $playlist->uri, 'playlist_id' => $playlist->id];
            $params['return'] = $return;

            $route = route('spotify.api.playlist.follow',$params);

            if ( ! AuthInterface::is_logged_in() ) {
                $route = route('spotify.login',['url'=>$route]);
            }
            return $route;
        }

        public static function save_link($playlist,$return='redirect',$name=false,$image=false) {
            $settings = Settings::find(Settings::$settingID);
            $name = $name ? $name : $settings->playlist_title;
            $image = $image ? $image : $settings->playlist_image;

            $params = ['name' => $name, 'image' => $image, 'playlist_id' => $playlist->id];
            $params['return'] = $return;

            $route = route('spotify.api.playlist.add', $params);
            $route = route('spotify.login', ['url' => $route]);
            // smart idea, but was a hassle for dev when we need to approve/un-approve apps when permissions change. Easier to log in every time.
//            if ( ! AuthInterface::is_logged_in() ) {
//                $route = route('spotify.login', ['url' => $route]);
//            }

            return $route;
        }

        public function follow_playlist(Request $request) {
            $playlist_uri = $request->input('playlist_uri');
            $playlist_id = $request->input('playlist_id');
            $return = $request->input('return');

            try {
                $api = APIInterface::getAPI();

                $uris = explode(':',$playlist_uri);

                $api->followPlaylist($uris[2], $uris[4]);

                // save as added
                Session::put('added_playlist_' . $playlist_id, $uris[4]);
//                Session::save();

                if ( $return == 'close' ) {
                    return Helpers::close_tab();
                } else if ( $return == 'spotify' ) {
                    $playlist = PlaylistInterface::get_playlist($uris[2], $uris[4]);
                    return Redirect::to($playlist->external_urls->spotify);
                }

                return Redirect::back();

            } catch (Exception $ex) {
                // Expired, need to log in again
                return Redirect::to(route('spotify.logout'));
            }
        }

        public function add_playlist(Request $request) {
            $name = $request->input('name');
            $image = $request->input('image');
            $tracklist = (array) $request->input('tracklist'); // optional
            $playlist_id = $request->input('playlist_id');
            $return = $request->input('return');

            try {
                if( ! $tracklist ){ // if not supplied, get from playlist
                    $playlist = Playlist::findOrFail($playlist_id);
                    $tracklist = json_decode($playlist->tracks);
                    $tracklist = Helpers::getIDs($tracklist);
                }

                $api = APIInterface::getAPI();
                $user = $api->me();

                // Create user playlist
                $user_playlist = $api->createUserPlaylist($user->id, ['name' => $name]);

                // add tracks to the playlist
                $api->addUserPlaylistTracks($user->id, $user_playlist->id, $tracklist);

                // add custom image if present
                if( @$image ){
                    $image = file_get_contents($image);
                    $image = base64_encode($image);
                    $api->addUserPlaylistImage($user->id, $user_playlist->id, $image);
                }

                // save as added
                Session::put('added_playlist_' . $playlist_id, $user_playlist->id);
//                Session::save();

                if( $return == 'close' ){
                    return Helpers::close_tab();
                }
                else if ( $return == 'spotify' ) {
                    return Redirect::to($user_playlist->external_urls->spotify);
                }
                return Redirect::back();

            } catch (Exception $ex) {
                $errorUrl = Helpers::merge_get_url(route('spotify.logout'), 'spotify_error=' . $ex->getMessage());
                $errorUrl = Helpers::merge_get_url($errorUrl, 'spotify_return=' . $return);
                return Redirect::to($errorUrl);
            }
        }

        public static function add_client_playlist($name,$tracklist,$playlist_id,$image=false) {
            try {
                $api = APIInterface::getClientAPI();
                $user = $api->me();

                // Create user playlist
                $client_playlist = $api->createUserPlaylist($user->id, ['name' => $name]);

                // add tracks to the playlist
                $api->addUserPlaylistTracks($user->id, $client_playlist->id, $tracklist);

                // add custom image if present
                if( @$image ){
                    $image = file_get_contents($image);
                    $image = base64_encode($image);
                    $api->addUserPlaylistImage($user->id, $client_playlist->id, $image);
                }

                // save as added
                Session::put('added_playlist_' . $playlist_id, $client_playlist->id);
//                Session::save();

                return $client_playlist;
            } catch (Exception $ex) {
//                dd( $ex );
                return false;
            }
        }
    }
