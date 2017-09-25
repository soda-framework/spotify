# Soda Spotify
A sweet spotify platform for Made in Katana

##Installation
1) Firstly follow the instructions to install Soda CMS at:
https://github.com/soda-framework/cms

2) Install Soda Spotify

```
#!json
composer require soda-framework/spotify
```

3) Install these packages using NPM.
These are needed for compiling the Vue Components in this package.
```
"vue-template-es2015-compiler": "1.3.1",
"vue": "^2.1.10",
"vue-template-compiler": "^2.1.10"
"axios": ""
```

4) Integrate Soda Spotify into laravel by adding `Soda\Spotify\Providers\SpotifyServiceProvider::class`
in the providers array in `/config/app.php`
```
    'providers' => [
        Soda\Providers\SodaServiceProvider::class,
        Soda\Spotify\Providers\SpotifyServiceProvider::class,
    ]
```

5) Run the database migrations `php artisan migrate` to generate the necessary tables.

6) In the CMS, go to the Spotify > Settings tab and specify the *Package* and *Action* that each created playlist should use.
You can also specify the Playlist Title and Playlist Image (optional) for a playlist created on a user account.

##Usage

###Saving a Playlist
Include this file to create a link that will create a playlist on a users account.
```
#!php
@include('soda-spotify::save',compact('playlist'))
```
Or, you can pass in custom text/views/html to be displayed.
```
#!php
@include('soda-spotify::save',['playlist'=>$playlist, 'save_text'=>'Save Playlist', 'saved_text'=>'Playlist Saved'])
```
You can specify the Title and Image the created playlist will have by going to the Spotify > Settings tab and in the cms and editing the Playlist Title and Playlist Image (optional).


This action will send them to Spotify and log them in.
They will be sent back to the /playlist/ID page with 
```
#!php
$_GET['from_spotify']
```
 present.


If this parameter is present AND the playlist has not already been listed as added (session), then it will add the playlist to the users account before sending them back to the playlist on the site.

###Tracks
You need to include the Tracks component in your Javascript using Vue.
e.g.

```
#!js
var Vue = require('vue');
var Tracks = require('../../../../../vendor/mik/spotify/resources/js/Components/Tracks.vue'); // path to the Tracks component
require('../vueConfig.js');

if( $('#playlist').length ) {
    var playlist_vm = new Vue({
        el: '#playlist',
        data: {
            token: document.head.querySelector("[name=csrf-token]").content,
        },
        created: function(){
            var me = this;
        },
        methods: {

        },
        components: {
            Tracks
        }
    });
}

```


Displaying the Player Interface
```
#!php
@include('soda-spotify::tracks',compact('playlist'))
```
Or, you can pass in custom text act act as a title. e.g.
```
#!php
@include('soda-spotify::tracks',['playlist'=>$playlist, 'title'=>'Now Playing'])
```


#Routes
###Save Playlist
```
#!php
route('spotify.login',['url'=>URL_TO_RETURN_AFTER_LOGIN])
```
###Get Playlist Tracks
```
#!php
route('spotify.api.tracks',['id'=>PLAYLIST_ID])
```
Response
![Screen Shot 2017-02-01 at 11.55.19 am.png](https://bitbucket.org/repo/BAbLeA/images/3741642079-Screen%20Shot%202017-02-01%20at%2011.55.19%20am.png)

#Storing Track Data

###Tracks need to be stored in the database in the JSON with the structure:

```
#!json

[{
    "id": "12D0n7hKpPcjuUpcbAKjjr",
    "name": "Don't Like.1",
    "preview_url": "https:\/\/p.scdn.co\/mp3-preview\/fef716a78bda6c3ce06f3a7b12d4ad5e47035984?cid=da37dbff0615468591361d57d2118b05",
    "uri": "spotify:track:12D0n7hKpPcjuUpcbAKjjr",
    "url": "https:\/\/open.spotify.com\/track\/12D0n7hKpPcjuUpcbAKjjr",
    "artists": [{
        "id": "5K4W6rqBFWDnAN6FQUkS6x",
        "name": "Kanye West",
        "uri": "spotify:artist:5K4W6rqBFWDnAN6FQUkS6x",
        "url": "https:\/\/open.spotify.com\/artist\/5K4W6rqBFWDnAN6FQUkS6x"
    }, {
        "id": "15iVAtD3s3FsQR4w1v6M0P",
        "name": "Chief Keef",
        "uri": "spotify:artist:15iVAtD3s3FsQR4w1v6M0P",
        "url": "https:\/\/open.spotify.com\/artist\/15iVAtD3s3FsQR4w1v6M0P"
    }],
}]
```
*There is a function to convert an array of Spotify Created tracks JSON to this format*
```
#!php
Soda\Spotify\Components\Helpers::reduceResults($tracks); // $tracks is an array of Spotify Created track objects
```

###Creating a Playlist
```
#!php
$playlist = \Soda\Spotify\Controllers\PlaylistController::create_seeded_playlist($seeds);
```
$seeds is an array of seeds as defined in https://developer.spotify.com/web-api/get-recommendations/.

e.g.
```
#!php
$seeds = [
    'target_valence'=>0.5,
    'seed_tracks'=>ARRAY_OF_SPOTIFY_TRACK_IDS
];
```
**Important**: You MUST have any one of *seed_genres*, *seed_tracks* or *seed_artists*.
