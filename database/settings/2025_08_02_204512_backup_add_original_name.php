<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Check if setting already exists before adding
        if (!$this->migrator->exists('backup.google_drive_service_account_original_name')) {
            $this->migrator->add('backup.google_drive_service_account_original_name', '');
        }
    }
};
