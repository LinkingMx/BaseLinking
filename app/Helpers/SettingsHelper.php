<?php

namespace App\Helpers;

use App\Settings\AppearanceSettings;
use App\Settings\BackupSettings;
use App\Settings\GeneralSettings;
use App\Settings\LocalizationSettings;
use Illuminate\Support\Facades\Schema;

class SettingsHelper
{
    /**
     * Check if settings table exists and is ready to use
     */
    public static function isAvailable(): bool
    {
        try {
            // Don't try to access settings during migrations or console commands that might not need them
            if (app()->runningInConsole()) {
                $command = $_SERVER['argv'][1] ?? '';
                if (in_array($command, ['migrate', 'migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback'])) {
                    return false;
                }
            }
            
            // Check if table exists and has data
            if (!Schema::hasTable('settings')) {
                return false;
            }
            
            // Check if settings have been initialized by looking for any general settings
            $count = \DB::table('settings')->where('group', 'general')->count();
            return $count > 0;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get general settings instance
     */
    public static function general(): GeneralSettings|MockGeneralSettings
    {
        if (!self::isAvailable()) {
            return new MockGeneralSettings();
        }

        try {
            return app(GeneralSettings::class);
        } catch (\Exception $e) {
            return new MockGeneralSettings();
        }
    }

    /**
     * Get appearance settings instance
     */
    public static function appearance(): AppearanceSettings|MockAppearanceSettings
    {
        if (!self::isAvailable()) {
            return new MockAppearanceSettings();
        }

        try {
            return app(AppearanceSettings::class);
        } catch (\Exception $e) {
            return new MockAppearanceSettings();
        }
    }

    /**
     * Get localization settings instance
     */
    public static function localization(): LocalizationSettings|MockLocalizationSettings
    {
        if (!self::isAvailable()) {
            return new MockLocalizationSettings();
        }

        try {
            return app(LocalizationSettings::class);
        } catch (\Exception $e) {
            return new MockLocalizationSettings();
        }
    }

    /**
     * Get backup settings instance
     */
    public static function backup(): BackupSettings|MockBackupSettings
    {
        if (!self::isAvailable()) {
            return new MockBackupSettings();
        }

        try {
            return app(BackupSettings::class);
        } catch (\Exception $e) {
            return new MockBackupSettings();
        }
    }

    /**
     * Get all settings as array
     */
    public static function all(): array
    {
        return [
            'general' => self::general()->toArray(),
            'appearance' => self::appearance()->toArray(),
            'localization' => self::localization()->toArray(),
            'backup' => self::backup()->toArray(),
        ];
    }

    /**
     * Get app name
     */
    public static function appName(): ?string
    {
        return self::general()->app_name;
    }

    /**
     * Get app description
     */
    public static function appDescription(): ?string
    {
        return self::general()->app_description;
    }

    /**
     * Get app logo URL
     */
    public static function appLogo(): ?string
    {
        return self::general()->getAppLogoUrlAttribute();
    }

    /**
     * Get dark mode logo URL
     */
    public static function darkModeLogo(): ?string
    {
        return self::appearance()->getDarkModeLogoUrlAttribute();
    }

    /**
     * Get contact email
     */
    public static function contactEmail(): ?string
    {
        return self::general()->contact_email;
    }

    /**
     * Get site URL
     */
    public static function siteUrl(): ?string
    {
        return self::general()->site_url;
    }

    /**
     * Get theme
     */
    public static function theme(): ?string
    {
        return self::appearance()->theme;
    }

    /**
     * Get primary color
     */
    public static function primaryColor(): ?string
    {
        return self::appearance()->primary_color;
    }

    /**
     * Get danger color
     */
    public static function dangerColor(): ?string
    {
        return self::appearance()->danger_color;
    }

    /**
     * Get gray color
     */
    public static function grayColor(): ?string
    {
        return self::appearance()->gray_color;
    }

    /**
     * Get info color
     */
    public static function infoColor(): ?string
    {
        return self::appearance()->info_color;
    }

    /**
     * Get success color
     */
    public static function successColor(): ?string
    {
        return self::appearance()->success_color;
    }

    /**
     * Get warning color
     */
    public static function warningColor(): ?string
    {
        return self::appearance()->warning_color;
    }

    /**
     * Get font family
     */
    public static function fontFamily(): ?string
    {
        return self::appearance()->font_family;
    }

    /**
     * Get default language
     */
    public static function defaultLanguage(): ?string
    {
        return self::localization()->default_language;
    }

    /**
     * Get timezone
     */
    public static function timezone(): ?string
    {
        return self::localization()->timezone;
    }

    /**
     * Get date format
     */
    public static function dateFormat(): ?string
    {
        return self::localization()->date_format;
    }

    /**
     * Get time format
     */
    public static function timeFormat(): ?string
    {
        return self::localization()->time_format;
    }

    /**
     * Get currency
     */
    public static function currency(): ?string
    {
        return self::localization()->currency;
    }

    /**
     * Format a date using the configured format
     */
    public static function formatDate($date): string
    {
        if (! $date) {
            return '';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        $timezone = self::timezone() ?: config('app.timezone', 'UTC');
        $format = self::dateFormat() ?: 'd/m/Y';

        return $date->setTimezone($timezone)->format($format);
    }

    /**
     * Format a time using the configured format
     */
    public static function formatTime($time): string
    {
        if (! $time) {
            return '';
        }

        if (is_string($time)) {
            $time = \Carbon\Carbon::parse($time);
        }

        $timezone = self::timezone() ?: config('app.timezone', 'UTC');
        $format = self::timeFormat() ?: 'H:i';

        return $time->setTimezone($timezone)->format($format);
    }

    /**
     * Format a datetime using the configured format
     */
    public static function formatDateTime($datetime): string
    {
        if (! $datetime) {
            return '';
        }

        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        $timezone = self::timezone() ?: config('app.timezone', 'UTC');
        $dateFormat = self::dateFormat() ?: 'd/m/Y';
        $timeFormat = self::timeFormat() ?: 'H:i';

        return $datetime->setTimezone($timezone)->format("{$dateFormat} {$timeFormat}");
    }

    /**
     * Get current date formatted with settings
     */
    public static function currentDate(): string
    {
        return self::formatDate(\Carbon\Carbon::now());
    }

    /**
     * Get current time formatted with settings
     */
    public static function currentTime(): string
    {
        return self::formatTime(\Carbon\Carbon::now());
    }

    /**
     * Get current datetime formatted with settings
     */
    public static function currentDateTime(): string
    {
        return self::formatDateTime(\Carbon\Carbon::now());
    }

    /**
     * Create a Carbon instance in the configured timezone
     */
    public static function now(): \Carbon\Carbon
    {
        $timezone = self::timezone() ?: config('app.timezone', 'UTC');

        return \Carbon\Carbon::now($timezone);
    }

    /**
     * Parse a date string in the configured timezone
     */
    public static function parseDate(string $date): \Carbon\Carbon
    {
        $timezone = self::timezone() ?: config('app.timezone', 'UTC');

        return \Carbon\Carbon::parse($date, $timezone);
    }

    /**
     * Format a monetary amount using the configured currency
     */
    public static function formatMoney(float $amount, bool $showSymbol = true): string
    {
        $currency = self::currency() ?: 'USD';

        $currencySymbols = [
            'MXN' => '$',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'JPY' => '¥',
            'CHF' => 'CHF',
            'COP' => '$',
            'ARS' => '$',
            'BRL' => 'R$',
            'CLP' => '$',
            'PEN' => 'S/',
        ];

        $symbol = $currencySymbols[$currency] ?? $currency;
        $formattedAmount = number_format($amount, 2);

        return $showSymbol ? $symbol.' '.$formattedAmount : $formattedAmount;
    }

    /**
     * Get currency symbol for the configured currency
     */
    public static function getCurrencySymbol(): string
    {
        $currency = self::currency() ?: 'USD';

        $currencySymbols = [
            'MXN' => '$',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'JPY' => '¥',
            'CHF' => 'CHF',
            'COP' => '$',
            'ARS' => '$',
            'BRL' => 'R$',
            'CLP' => '$',
            'PEN' => 'S/',
        ];

        return $currencySymbols[$currency] ?? $currency;
    }

    /**
     * Convert hex color to Filament color array
     */
    public static function hexToFilamentColor(string $hex): array
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Generate color shades
        return [
            '50' => self::adjustBrightness($r, $g, $b, 0.95),
            '100' => self::adjustBrightness($r, $g, $b, 0.9),
            '200' => self::adjustBrightness($r, $g, $b, 0.8),
            '300' => self::adjustBrightness($r, $g, $b, 0.6),
            '400' => self::adjustBrightness($r, $g, $b, 0.4),
            '500' => sprintf('#%02x%02x%02x', $r, $g, $b), // Original color
            '600' => self::adjustBrightness($r, $g, $b, -0.1),
            '700' => self::adjustBrightness($r, $g, $b, -0.2),
            '800' => self::adjustBrightness($r, $g, $b, -0.3),
            '900' => self::adjustBrightness($r, $g, $b, -0.4),
            '950' => self::adjustBrightness($r, $g, $b, -0.5),
        ];
    }

    /**
     * Adjust brightness of RGB color
     */
    private static function adjustBrightness(int $r, int $g, int $b, float $factor): string
    {
        if ($factor > 0) {
            // Lighten
            $r = min(255, $r + (255 - $r) * $factor);
            $g = min(255, $g + (255 - $g) * $factor);
            $b = min(255, $b + (255 - $b) * $factor);
        } else {
            // Darken
            $factor = abs($factor);
            $r = max(0, $r * (1 - $factor));
            $g = max(0, $g * (1 - $factor));
            $b = max(0, $b * (1 - $factor));
        }

        return sprintf('#%02x%02x%02x', (int) $r, (int) $g, (int) $b);
    }

    /**
     * Get Filament colors based on appearance settings
     */
    public static function getFilamentColors(): array
    {
        $appearance = self::appearance();

        return [
            'primary' => self::hexToFilamentColor($appearance->primary_color ?? '#f59e0b'),
            'danger' => self::hexToFilamentColor($appearance->danger_color ?? '#ef4444'),
            'gray' => self::hexToFilamentColor($appearance->gray_color ?? '#71717a'),
            'info' => self::hexToFilamentColor($appearance->info_color ?? '#3b82f6'),
            'success' => self::hexToFilamentColor($appearance->success_color ?? '#10b981'),
            'warning' => self::hexToFilamentColor($appearance->warning_color ?? '#f59e0b'),
        ];
    }
}

/**
 * Mock classes for when settings are not available
 */

class MockGeneralSettings
{
    public $app_name = 'SaaS Helpdesk';
    public $app_description = 'Sistema de gestión de helpdesk y soporte técnico';
    public $app_logo = null;
    public $contact_email = 'support@example.com';
    public $site_url;

    public function __construct()
    {
        $this->site_url = config('app.url');
    }

    public function toArray(): array
    {
        return [
            'app_name' => $this->app_name,
            'app_description' => $this->app_description,
            'app_logo' => $this->app_logo,
            'contact_email' => $this->contact_email,
            'site_url' => $this->site_url,
        ];
    }

    public function getAppLogoUrlAttribute(): ?string
    {
        return asset('logo.svg');
    }
}

class MockAppearanceSettings
{
    public $theme = 'light';
    public $primary_color = '#3b82f6';
    public $danger_color = '#ef4444';
    public $gray_color = '#6b7280';
    public $info_color = '#3b82f6';
    public $success_color = '#10b981';
    public $warning_color = '#f59e0b';
    public $font_family = 'Inter';
    public $dark_mode_logo = null;

    public function toArray(): array
    {
        return [
            'theme' => $this->theme,
            'primary_color' => $this->primary_color,
            'danger_color' => $this->danger_color,
            'gray_color' => $this->gray_color,
            'info_color' => $this->info_color,
            'success_color' => $this->success_color,
            'warning_color' => $this->warning_color,
            'font_family' => $this->font_family,
            'dark_mode_logo' => $this->dark_mode_logo,
        ];
    }

    public function getDarkModeLogoUrlAttribute(): ?string
    {
        return asset('logo.svg');
    }
}

class MockLocalizationSettings
{
    public $default_language = 'en';
    public $timezone = 'UTC';

    public function toArray(): array
    {
        return [
            'default_language' => $this->default_language,
            'timezone' => $this->timezone,
        ];
    }
}

class MockBackupSettings
{
    public $google_drive_enabled = false;
    public $google_drive_service_account_path = null;
    public $google_drive_service_account_original_name = null;
    public $google_drive_folder_id = null;

    public function toArray(): array
    {
        return [
            'google_drive_enabled' => $this->google_drive_enabled,
            'google_drive_service_account_path' => $this->google_drive_service_account_path,
            'google_drive_service_account_original_name' => $this->google_drive_service_account_original_name,
            'google_drive_folder_id' => $this->google_drive_folder_id,
        ];
    }

    public function isGoogleDriveConfigured(): bool
    {
        return false;
    }

    public function getGoogleDriveCredentials(): ?array
    {
        return null;
    }
}
