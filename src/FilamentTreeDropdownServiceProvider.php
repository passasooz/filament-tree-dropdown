<?php

namespace Passasooz\FilamentTreeDropdown;

use Illuminate\Support\ServiceProvider;

class FilamentTreeDropdownServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-tree-dropdown');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/filament-tree-dropdown'),
            ], 'filament-tree-dropdown-views');
        }
    }
}
