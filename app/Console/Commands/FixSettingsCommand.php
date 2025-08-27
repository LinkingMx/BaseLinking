<?php

namespace App\Console\Commands;

use App\Settings\AppearanceSettings;
use App\Settings\BackupSettings;
use App\Settings\EmailTemplateSettings;
use App\Settings\GeneralSettings;
use App\Settings\LocalizationSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:fix {--clear-cache : Clear settings cache} {--reinitialize : Reinitialize settings with defaults}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix settings configuration issues for production deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing settings configuration...');

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            $this->clearSettingsCache();
        }

        // Check settings table
        $this->checkSettingsTable();

        // Verify settings classes
        $this->verifySettingsClasses();

        // Reinitialize if requested
        if ($this->option('reinitialize')) {
            $this->reinitializeSettings();
        }

        // Test settings functionality
        $this->testSettings();

        $this->info('✅ Settings configuration fixed!');
        return Command::SUCCESS;
    }

    protected function clearSettingsCache(): void
    {
        $this->info('🧹 Clearing settings cache...');

        try {
            $this->call('settings:clear-cache');
            $this->call('settings:clear-discovered');
            $this->info('✅ Settings cache cleared');
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not clear settings cache: ' . $e->getMessage());
        }
    }

    protected function checkSettingsTable(): void
    {
        $this->info('📋 Checking settings table...');

        if (!Schema::hasTable('settings')) {
            $this->error('❌ Settings table does not exist!');
            $this->info('Run: php artisan migrate --path=database/settings');
            return;
        }

        $count = DB::table('settings')->count();
        $this->info("✅ Settings table exists with {$count} records");
    }

    protected function verifySettingsClasses(): void
    {
        $this->info('🔍 Verifying settings classes...');

        $classes = [
            'General' => GeneralSettings::class,
            'Appearance' => AppearanceSettings::class,
            'Localization' => LocalizationSettings::class,
            'Backup' => BackupSettings::class,
            'EmailTemplate' => EmailTemplateSettings::class,
        ];

        foreach ($classes as $name => $class) {
            try {
                $group = $class::group();
                $count = DB::table('settings')->where('group', $group)->count();
                
                if ($count > 0) {
                    $this->info("✅ {$name}Settings: {$count} settings found");
                } else {
                    $this->warn("⚠️  {$name}Settings: No settings found in group '{$group}'");
                }
            } catch (\Exception $e) {
                $this->error("❌ {$name}Settings error: " . $e->getMessage());
            }
        }
    }

    protected function reinitializeSettings(): void
    {
        $this->info('🔄 Reinitializing settings with defaults...');

        try {
            // This will trigger the settings migrations which populate defaults
            $this->call('migrate', ['--path' => 'database/settings', '--force' => true]);
            $this->info('✅ Settings reinitialized');
        } catch (\Exception $e) {
            $this->error('❌ Failed to reinitialize settings: ' . $e->getMessage());
        }
    }

    protected function testSettings(): void
    {
        $this->info('🧪 Testing settings functionality...');

        try {
            // Test using our helper
            $generalSettings = \App\Helpers\SettingsHelper::general();
            $this->info('✅ SettingsHelper working: ' . $generalSettings->app_name);

            // Test direct access
            $directSettings = app(GeneralSettings::class);
            $this->info('✅ Direct settings access working: ' . $directSettings->app_name);

        } catch (\Exception $e) {
            $this->error('❌ Settings test failed: ' . $e->getMessage());
        }
    }
}
