<?php
    namespace Soda\Spotify\Models;

    use Carbon\Carbon;
    use Illuminate\Foundation\Auth\User as Authenticatable;

    class User extends Authenticatable {

        protected $table = 'spotify_users';
        protected $keyType = 'string';
        protected $fillable = [
            'id',
            'display_name',
            'url',
            'uri',
            'image',
            'follower_count',
            'country',
            'access_token',
            'access_token_expiration',
            'refresh_token',
            'last_loggedin_at',
        ];

        /**
         * The attributes that should be mutated to dates.
         *
         * @var array
         */
        protected $dates = [
            'created_at',
            'updated_at',
            'last_loggedin_at',
        ];

        public function updateLoginTimestamp() {
            $this->setAttribute('last_loggedin_at', Carbon::now());
            $this->save();
        }

        public function playlist() {
            return $this->hasMany(Playlist::class);
        }

        public function getName() {
            return $this->display_name ? $this->display_name : $this->user_id;
        }
    }
