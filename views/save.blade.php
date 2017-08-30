<?php
    use Soda\Spotify\Api\SpotifyInterface;

    $save = @$save_text ? $save_text : 'Save Playlist';
    $saved = @$saved_text ? $saved_text : 'Playlist Saved';
?>

<a href="{{ route('spotify.login',['url'=>URL::current()]) }}">
    {!! SpotifyInterface::playlist_added(@$playlist->id) ? $saved : $save !!}
</a>
