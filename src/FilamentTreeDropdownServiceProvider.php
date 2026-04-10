<?php

namespace Passasooz\FilamentTreeDropdown;

use Illuminate\Support\ServiceProvider;

class FilamentTreeDropdownServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-tree-dropdown');
    }
}
