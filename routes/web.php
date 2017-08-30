<?php
    use Illuminate\Support\Facades\Schema;
    use Soda\Spotify\Models\Settings;
    
    Route::group(['prefix' => 'spotify'], function() {
        Route::get('logout', 'Soda\Spotify\Api\AuthInterface@logout')->name('spotify.logout');
        Route::get('login', 'Soda\Spotify\Api\AuthInterface@login')->name('spotify.login');
        Route::get('login-go', 'Soda\Spotify\Api\AuthInterface@login_go')->name('spotify.login.go');
        Route::get('login-return', 'Soda\Spotify\Api\AuthInterface@login_return')->name('spotify.login.return');

        Route::group(['prefix' => 'token'], function(){
            Route::get('login', 'Soda\Spotify\Controllers\TokenController@login')->name('spotify.token.login');
            Route::get('login-go', 'Soda\Spotify\Controllers\TokenController@login_go')->name('spotify.token.login.go');
            Route::get('login-return', 'Soda\Spotify\Controllers\TokenController@login_return')->name('spotify.token.login.return');
            Route::get('save', 'Soda\Spotify\Controllers\TokenController@login_return')->name('spotify.token.save');
        });
    });

    Route::group(['prefix' => config('soda.cms.path'), 'middleware' => 'soda.auth:soda'], function(){
        Route::group(['prefix' => 'spotify'], function(){
            Route::group(['prefix' => 'playlists'], function(){
                Route::get('/', '\Soda\Spotify\Controllers\PlaylistController@anyIndex')
                    ->name('spotify.playlists');
                Route::get('playlist/{id?}', '\Soda\Spotify\Controllers\PlaylistController@getPlaylist')
                    ->name('spotify.playlists.get.playlist');
            });

            Route::group(['prefix' => 'settings'], function(){
                Route::get('/', '\Soda\Spotify\Controllers\SettingsController@getModify')
                    ->name('spotify.settings');
                Route::post('modify', '\Soda\Spotify\Controllers\SettingsController@postModify')
                    ->name('spotify.settings.post.modify');
            });
        });
    });

    if ( ! app()->runningInConsole() && Schema::hasTable((new Settings)->getTable()) ) {
        $settings = Settings::find(Settings::$settingID);
        if ( $settings && ( isset($settings->package) || isset($settings->action) ) ) {
            Route::get('/playlist/{id}', '\Soda\Spotify\Controllers\PageController@playlist')->name('spotify.playlist');
        }
    }
