<?php

namespace Passasooz\FilamentTreeDropdown;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentTreeDropdownPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-tree-dropdown';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
