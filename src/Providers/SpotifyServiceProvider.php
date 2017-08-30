<?php
namespace Soda\Spotify\Providers;

use Illuminate\Support\Facades\View;
use Route;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class SpotifyServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */

    protected $defer = false;

//    protected $namespace = 'Soda\Spotify';

    public function boot()
    {
        parent::boot();

        // Publishing configs
        $this->publishes([__DIR__.'/../../config/' => config_path('soda')], 'soda.spotify');

        $this->loadViewsFrom(__DIR__ . '/../../views', 'soda-spotify');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->app['config']->set('auth.providers.soda-spotify', $this->app->config->get('soda.spotify.auth.provider'));
        $this->app['config']->set('auth.guards.soda-spotify', $this->app->config->get('soda.spotify.auth.guard'));
        $this->app['config']->set('auth.passwords.soda-spotify', $this->app->config->get('soda.spotify.auth.password'));

        app('soda.menu')->menu('sidebar', function ($menu) {
            $menu->addItem('Spotify', [
                'url'         => route('spotify.playlists'),
                'icon'        => 'fa fa-spotify',
                'label'       => 'Spotify',
                'isCurrent'   => soda_request_is('spotify*'),
                'permissions' => 'access-cms',
            ]);

            $menu['Spotify']->addChild('Playlists', [
                'url'         => route('spotify.playlists'),
                'icon'        => 'fa fa-music',
                'label'       => 'Playlists',
                'isCurrent'   => soda_request_is('spotify/playlists*'),
                'permissions' => 'access-cms',
            ]);

            $menu['Spotify']->addChild('Settings', [
                'url'         => route('spotify.settings'),
                'icon'        => 'fa fa-cog',
                'label'       => 'Settings',
                'isCurrent'   => soda_request_is('spotify/settings*'),
                'permissions' => 'access-cms',
            ]);
        });

        View::creator('soda::dashboard', function($view){
            $view->getFactory()->inject('main-content-outer', view('soda-spotify::cms.dashboard.main'));
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require_once __DIR__ . '/../../routes/web.php';
            require_once __DIR__ . '/../../routes/api.php';
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/spotify.php', 'soda.spotify');
    }
}
