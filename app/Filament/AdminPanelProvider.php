<?php

namespace App\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Enums\ThemeMode;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\ContentNodeResource;
use App\Filament\Resources\LocationResource;
use App\Filament\Resources\TaxonomyResource;
use App\Filament\Resources\KeywordResource;
use App\Filament\Resources\LiveDataVaultResource;
use App\Filament\Resources\GlobalAdBlockResource;
use App\Filament\Resources\PostTemplateResource;
use Illuminate\Auth\Middleware\Authenticate as BaseAuthenticate;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->profile()
            ->colors([
                'primary' => [
                    50 => '#ecfeff',
                    100 => '#cffafe',
                    200 => '#a5f3fc',
                    300 => '#67e8f9',
                    400 => '#22d3ee',
                    500 => '#06b6d4',
                    600 => '#0891b2',
                    700 => '#0e7490',
                    800 => '#155e75',
                    900 => '#164e63',
                    950 => '#083344',
                ],
                'gray' => Color::Zinc,
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->defaultThemeMode(ThemeMode::System)
            ->brandName('🚀 Omni Portal')
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.svg'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->navigationGroups([
                NavigationGroup::make('🚀 ENGINE ROOM')
                    ->icon('heroicon-o-cpu-chip')
                    ->collapsed(false)
                    ->items([
                        NavigationItem::make('Dashboard')
                            ->icon('heroicon-o-chart-pie')
                            ->url(fn () => route('filament.admin.pages.dashboard'))
                            ->isActive(),
                        NavigationItem::make('Live Data Vaults')
                            ->icon('heroicon-o-server-stack')
                            ->url(fn () => LiveDataVaultResource::getUrl()),
                        NavigationItem::make('Global Ad Blocks')
                            ->icon('heroicon-o-adjustments-vertical')
                            ->url(fn () => GlobalAdBlockResource::getUrl()),
                    ]),
                NavigationGroup::make('📊 THE DATA MATRIX')
                    ->icon('heroicon-o-chart-bar-square')
                    ->collapsed(false)
                    ->items([
                        NavigationItem::make('Locations')
                            ->icon('heroicon-o-map-pin')
                            ->url(fn () => LocationResource::getUrl()),
                        NavigationItem::make('Taxonomies')
                            ->icon('heroicon-o-folder-arrow-down')
                            ->url(fn () => TaxonomyResource::getUrl()),
                        NavigationItem::make('Keywords')
                            ->icon('heroicon-o-sparkles')
                            ->url(fn () => KeywordResource::getUrl()),
                        NavigationItem::make('Content Nodes')
                            ->icon('heroicon-o-document-text')
                            ->url(fn () => ContentNodeResource::getUrl()),
                        NavigationItem::make('Post Templates')
                            ->icon('heroicon-o-rectangle-stack')
                            ->url(fn () => PostTemplateResource::getUrl()),
                    ]),
                NavigationGroup::make('🤖 AI QUALITY ENGINE')
                    ->icon('heroicon-o-sparkles')
                    ->collapsed(false)
                    ->items([
                        NavigationItem::make('Quality Dashboard')
                            ->icon('heroicon-o-sparkles')
                            ->url(fn () => \App\Filament\Pages\QualityDashboard::getUrl()),
                    ]),
                NavigationGroup::make('🛡️ POLICY CONTROL')
                    ->icon('heroicon-o-shield-check')
                    ->collapsed(false)
                    ->items([
                        NavigationItem::make('Sanctum Tokens')
                            ->icon('heroicon-o-key')
                            ->url(fn () => route('filament.admin.resources.personal-access-tokens.index')),
                        NavigationItem::make('Admin Users')
                            ->icon('heroicon-o-users')
                            ->url(fn () => route('filament.admin.resources.users.index')),
                        NavigationItem::make('Queue Logs')
                            ->icon('heroicon-o-queue-list')
                            ->url(fn () => route('filament.admin.pages.dashboard')),
                    ]),
            ])
            ->middleware([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Filament\Http\Middleware\DisableBladeIconComponents::class,
                \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
            ])
            ->authGuard('web')
            ->authMiddleware([BaseAuthenticate::class])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('20rem')
            ->maxContentWidth('full')
            ->breadcrumbs(false)
            ->topNavigation(fn () => true);
    }
}