<?php
    Route::group(['prefix' => 'spotify'], function() {
        Route::group(['prefix' => 'api'], function() {
            Route::get('add-playlist', 'Soda\Spotify\Api\PlaylistInterface@add_playlist')->name('spotify.api.playlist.add');
            Route::get('follow-playlist', 'Soda\Spotify\Api\PlaylistInterface@follow_playlist')->name('spotify.api.playlist.follow');
            Route::get('add-client-playlist', 'Soda\Spotify\Api\PlaylistInterface@add_client_playlist')->name('spotify.api.client-playlist.add');
            Route::post('tracks/{id}', '\Soda\Spotify\Controllers\PlaylistController@tracks')->name('spotify.api.tracks');
        });
    });
