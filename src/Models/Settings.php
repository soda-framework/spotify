<?php
    namespace Soda\Spotify\Models;
    use Illuminate\Database\Eloquent\Model;

    class Settings extends Model{
        const SETTING_ID = 1;

        protected $table = 'spotify_settings';
        protected $fillable = [
            'playlist_title',
            'playlist_description',
            'playlist_image',
            'created_at',
            'updated_at',
        ];
    }
