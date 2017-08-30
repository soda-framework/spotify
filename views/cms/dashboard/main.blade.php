<?php
    use Soda\Spotify\Models\Playlist;
    use Soda\Spotify\Models\User;

    $playlistsCount = Playlist::where('tracks','!=',null)->count();
    $usersCount = User::count();
?>

<style>
    .content-block, .display-3 {
        margin-top: 0;
    }
</style>
<div class="content-top">
    <h1>
        <span>Dashboard</span>
    </h1>
</div>
<div class="row">
    <div class="col-xs-12 col-md-3">
        <div class="content-block">
            <h2 class="display-3">
                <span>
                    {{ $usersCount }}
                </span>
            </h2>
            <small class="text-muted">Spotify Users</small>
        </div>
        <div class="content-block">
            <h2 class="display-3">
                <span>
                    {{ $playlistsCount }}
                </span>
            </h2>
            <small class="text-muted">User Playlists</small>
        </div>
    </div>
</div>
