<?php

return [

    'panel_providers' => [
        \App\Filament\AdminPanelProvider::class,
    ],

    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    'assets_path' => null,

    'cache_path' => base_path('bootstrap/cache/filament'),

    'livewire_loading_delay' => 'default',

    'system_route_prefix' => 'filament',

    'default_theme' => \Filament\Themes\Theme::class,

];
