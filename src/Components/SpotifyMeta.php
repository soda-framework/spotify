<?php

    namespace Soda\Spotify\Components;

    class SpotifyMeta
    {
        public static $application_id = 1;

        public static $playlist_page_type = [
            'name' => 'Playlist',
            'description' => 'Playlist',
            'identifier' => 'playlist',
        ];
        public static $playlist_page = [
            'name' => 'Playlist',
            'slug' => '/playlist',
        ];
        public static $playlist_block_type = [
            'name' => 'Playlists',
            'description' => 'Playlists',
            'identifier' => 'playlists',
        ];
        public static $playlist_block = [
            'name' => 'Playlists',
            'description' => 'Playlists',
            'identifier' => 'playlists',
        ];
    }
