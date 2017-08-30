<?php
error_reporting(-1);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

$session = new Soda\Spotify\Api\Session(
    'CLIENT_ID',
    'CLIENT_SECRET',
    'REDIRECT_URI'
);

$api = new Soda\Spotify\Api\Client();

if (isset($_GET['code'])) {
    $session->requestAccessToken($_GET['code']);
    $api->setAccessToken($session->getAccessToken());

    print_r($api->me());
} else {
    $scopes = [
        'scope' => [
            'user-read-email',
            'user-library-modify',
        ],
    ];

    header('Location: ' . $session->getAuthorizeUrl($scopes));
}
