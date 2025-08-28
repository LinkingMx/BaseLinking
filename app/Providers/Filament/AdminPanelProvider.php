<?php

namespace App\Providers\Filament;

use App\Helpers\SettingsHelper;
use BezhanSalleh\FilamentExceptions\FilamentExceptionsPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentView;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Load settings
        $generalSettings = SettingsHelper::general();
        $appearanceSettings = SettingsHelper::appearance();

        // Note: Localization settings are now applied globally in AppServiceProvider

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName($generalSettings->app_name ?? 'SaaS Helpdesk')
            ->brandLogo($generalSettings->app_logo ? asset('storage/'.$generalSettings->app_logo) : null)
            ->brandLogoHeight('2rem')
            ->darkModeBrandLogo($appearanceSettings->dark_mode_logo ? asset('storage/'.$appearanceSettings->dark_mode_logo) : null)
            ->login()
            // REGISTRO DESHABILITADO: Para habilitar registro nuevamente, descomente la siguiente línea:
            // ->registration()
            ->passwordReset()
            ->emailVerification()
            ->colors(SettingsHelper::getFilamentColors())
            ->font($appearanceSettings->font_family ?? 'Inter')

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                \Z3d0X\FilamentLogger\Resources\ActivityResource::class,
                \App\Filament\Resources\WorkflowWizardResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->plugins([
                \RickDBCN\FilamentEmail\FilamentEmail::make(),
                FilamentShieldPlugin::make(),
                FilamentMenuBuilderPlugin::make()
                    ->navigationGroup('Configuración')
                    ->navigationLabel('Menús Personalizados'),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: true
                    )
                    ->enableTwoFactorAuthentication()
                    ->enableSanctumTokens(),
                FilamentExceptionsPlugin::make()
                    ->navigationGroup('Monitoreo y Logs')
                    ->navigationLabel('Visor de Excepciones')
                    ->navigationSort(3),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                name: 'panels::head.end',
                hook: fn (): string => app('custom-styles')->render('admin')
            );
    }
}
