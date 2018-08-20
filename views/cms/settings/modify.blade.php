<?php
    use \Soda\Spotify\Controllers\TokenController;
?>

@extends(soda_cms_view_path('layouts.inner'))

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('soda.home') }}">Home</a></li>
        <li><a href="{{ route('spotify.settings') }}">Settings</a></li>
        <li class="active">Edit</li>
    </ol>
@stop

@section('head.cms')
    <title>Spotify | Settings | Edit</title>
@endsection

@section('content-heading-button')
    @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#settings-form'])
@endsection

@include(soda_cms_view_path('partials.heading'), [
    'icon'        => 'fa fa-cog',
    'title'       => 'Editing Settings',
])
@section('content')
    <div class="content-block">
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="post" action="{{ route('spotify.settings.post.modify') }}" id="settings-form">
            <input type="hidden" name="id" value="{{ @$settings->id }}" />
            {!! csrf_field() !!}

            {!! app('soda.form')->text([
                'name'        => 'Client ID*',
                'field_name'  => 'client_id',
            ])->setModel($settings) !!}

            {!! app('soda.form')->text([
                'name'        => 'Client Secret*',
                'field_name'  => 'client_secret',
            ])->setModel($settings) !!}

            <hr/>

            Specify the Package and Action that each created playlist should use.
            <br/>
            <br/>

            {!! app('soda.form')->text([
                'name'        => 'Package',
                'field_name'  => 'package',
            ])->setModel($settings) !!}

            {!! app('soda.form')->text([
                'name'        => 'Action',
                'field_name'  => 'action',
            ])->setModel($settings) !!}

            <hr/>

            The name and image for a playlist that will appear in Spotify.
            <br/>
            <br/>

            {!! app('soda.form')->text([
                'name'        => 'Playlist Title',
                'field_name'  => 'playlist_title',
            ])->setModel($settings) !!}

            {!! app('soda.form')->text([
                'name'        => 'Playlist Description',
                'field_name'  => 'playlist_description',
            ])->setModel($settings) !!}

            {!! app('soda.form')->fancy_upload([
                'name'        => 'Playlist Image',
                'field_name'  => 'playlist_image',
            ])->setModel($settings) !!}

            <br/>
            <br/>

            <b>* Required Field</b>

            <hr/>

            Click <b>GENERATE TOKEN</b> and authenticate using the account that playlists should be created under.
            {{--<br/>--}}
            {{--Leave blank if playlist should be created on the users accounts.--}}
            <br/>
            <br/>

            <fieldset class="form-group row field_token_user ">
                <label class="col-sm-2" for="field_token_user">Token User</label>
                <div class="col-sm-10">
                    <a href="{{ $settings->token_user_link }}" target="_blank">
                        {{ $settings->token_user }}
                    </a>
                    <br>
                </div>
            </fieldset>

            {!! app('soda.form')->static_text([
                'name'        => 'Access Token',
                'field_name'  => 'access_token',
            ])->setModel($settings) !!}
            {!! app('soda.form')->static_text([
                'name'        => 'Access Token Expiration' . (TokenController::isTokenExpired() ? ' (expired)' : ''),
                'field_name'  => 'access_token_expiration',
            ])->setModel($settings) !!}
            {!! app('soda.form')->static_text([
                'name'        => 'Refresh Token',
                'field_name'  => 'refresh_token',
            ])->setModel($settings) !!}

            <a href="{{ route('spotify.token.login',['url'=>route('spotify.token.save')]) }}">
                <div class="btn btn-info btn-lg">
                    <i class="fa fa-barcode"></i>
                    <span>GENERATE TOKEN</span>
                </div>
            </a>

        </form>
    </div>

    <div class="content-bottom">
        @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#settings-form'])
    </div>
@endsection
