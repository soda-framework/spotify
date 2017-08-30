<?php
    return [
        'login_scopes' => [
            'user-read-private',
            'playlist-modify-public',
            'playlist-modify-private',
            'user-library-modify',
            'ugc-image-upload',
            'user-top-read',
        ],
        'allow_explicit'=>false,
        'auth' => [
            'provider' => [
                'driver' => 'eloquent',
                'model'  => Soda\Spotify\Models\User::class,
            ],
            'guard'    => [
                'driver'   => 'session',
                'provider' => 'soda-spotify',
            ],
            'password' => [
                'provider' => 'soda-spotify',
                'email'    => 'auth.emails.password',
                'table'    => 'password_resets',
                'expire'   => 60,
            ],
        ],
    ];
