<?php

namespace Modules\SponsorPortal\Providers;

use Illuminate\Support\ServiceProvider;

class SponsorPortalServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'SponsorPortal';
    protected string $moduleNameLower = 'sponsorportal';

    public function boot(): void
    {
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, '/Database/Migrations'));

        config(['auth.guards.sponsor' => [
            'driver'   => 'session',
            'provider' => 'sponsors',
        ]]);
        config(['auth.providers.sponsors' => [
            'driver' => 'eloquent',
            'model'  => \Modules\SponsorPortal\Models\Sponsor::class,
        ]]);
        config(['auth.passwords.sponsors' => [
            'provider' => 'sponsors',
            'table'    => 'password_resets',
            'expire'   => 60,
        ]]);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    protected function registerViews(): void
    {
        $viewPath   = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, '/Resources/views');
        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower . '-module-views']);
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
