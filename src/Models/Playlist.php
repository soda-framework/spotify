<?php
    namespace Soda\Spotify\Models;
    use Illuminate\Database\Eloquent\Model;

    class Playlist extends Model{
        protected $table = 'spotify_playlists';
        protected $fillable = [
            'tracks',
            'created_at',
            'updated_at',
        ];

        public function user() {
            return $this->belongsTo(User::class);
        }
    }
