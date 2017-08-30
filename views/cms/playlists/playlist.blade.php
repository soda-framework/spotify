@extends(soda_cms_view_path('layouts.inner'))

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('soda.home') }}">Home</a></li>
        <li><a href="{{ route('spotify.playlists') }}">Categories</a></li>
        <li class="active">Edit</li>
    </ol>
@stop

@section('head.cms')
    <title>Voting | Categories | Edit</title>
@endsection

@section('content-heading-button')
    @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#category-form'])
@endsection

@include(soda_cms_view_path('partials.heading'), [
    'icon'        => 'fa fa-music',
    'title'       => 'Viewing Playlist',
])
@section('content')
    <div class="content-block">
        <?php
            $playlist->tracks = json_decode($playlist->tracks, true);
        ?>
        {!! app('soda.form')->json([
            'name'        => 'Tracks',
            'field_name'  => 'tracks',
        ])->setModel($playlist) !!}
    </div>
@endsection
