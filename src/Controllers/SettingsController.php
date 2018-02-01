<?php
    namespace Soda\Spotify\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Redirect;
    use Soda\Spotify\Components\SpotifyMeta;
    use Soda\Spotify\Models\Settings;
    use Soda\Cms\Http\Controllers\BaseController;
    use Soda;
    use Symfony\Component\Debug\Exception;
    use Zofe\Rapyd\DataFilter\DataFilter;
    use Zofe\Rapyd\DataGrid\DataGrid;

    class SettingsController extends BaseController {
        public function getModify()
        {
            $settings = Settings::findOrNew(Settings::SETTING_ID);
            return view('soda-spotify::cms.settings.modify', compact('settings'));
        }

        public function postModify(Request $request)
        {
            $this->validate($request, [
                'client_id' => 'required|max:255',
                'client_secret' => 'required|max:255',
                'package' => 'max:255',
                'action' => 'max:255',
                'playlist_title' => 'max:255',
            ]);

            $settings = ($request->has('id') && !is_null($request->input('id')))? Settings::find($request->input('id')) : new Settings;
            $settings->id = Settings::SETTING_ID;
            $settings->client_id = $request->input('client_id');
            $settings->client_secret = $request->input('client_secret');
            $settings->package = $request->input('package');
            $settings->action = $request->input('action');
            $settings->playlist_title = $request->input('playlist_title');
            $settings->save();
            return redirect()->route('spotify.settings', ['settings' => $settings])->with('success', 'Successfully updated settings');
        }

        public static function clientId(){
            $settings = Settings::find(Settings::SETTING_ID);
            return $settings ? $settings->client_id : false;
        }
        public static function clientSecret(){
            $settings = Settings::find(Settings::SETTING_ID);
            return $settings ? $settings->client_secret : false;
        }
    }
