<?php

namespace Modules\Accounting\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\\Accounting\\Http\\Controllers';

    public function boot(): void { parent::boot(); }

    public function map(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Accounting', '/Routes/web.php'));
    }
}
