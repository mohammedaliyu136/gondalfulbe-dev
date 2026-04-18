<?php

namespace Modules\LandingPage\Providers;

use Illuminate\Support\ServiceProvider;

class AddMenuProvider extends ServiceProvider
{


    public function boot()
    {
        // Register a single global composer instead of one per route (was 1800+ registrations).
        // Guard so the landing-page menu only injects on actual landing-page routes.
        view()->composer('*', function ($view) {
            $routeName = optional(\Route::current())->getName() ?? '';
            if (str_starts_with($routeName, 'landingpage.') || str_starts_with($routeName, 'landing.')) {
                $view->getFactory()->startPush('add_menu', view('landingpage::menu.landingpage'));
            }
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
