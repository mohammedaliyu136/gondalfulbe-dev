<?php

namespace Modules\SponsorPortal\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\SponsorPortal\Http\Middleware\SponsorAuthenticate;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\\SponsorPortal\\Http\\Controllers';

    public function boot(): void
    {
        // Register the sponsor.auth middleware alias so routes can use it
        $this->app['router']->aliasMiddleware('sponsor.auth', SponsorAuthenticate::class);

        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('SponsorPortal', '/Routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace . '\\Api')
            ->group(module_path('SponsorPortal', '/Routes/api.php'));
    }
}
