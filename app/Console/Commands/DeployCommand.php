<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy {--production : Execute in production mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy the application with all necessary migrations including settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isProduction = $this->option('production');
        
        $this->info('🚀 Starting deployment process...');
        
        // Pre-deployment validation
        $this->info('🔍 Running pre-deployment validation...');
        $this->validateEnvironment();
        
        if ($isProduction) {
            $this->info('⚠️  Production mode enabled');
            
            // Maintenance mode
            $this->info('🔧 Enabling maintenance mode...');
            $this->call('down', ['--retry' => 60]);
        }
        
        try {
            // Clear caches first to avoid conflicts
            $this->info('🧹 Clearing caches...');
            $this->call('config:clear');
            $this->call('cache:clear');
            $this->call('view:clear');
            $this->call('route:clear');
            
            // Standard Laravel migrations
            $this->info('📦 Running Laravel migrations...');
            $this->call('migrate', $isProduction ? ['--force' => true] : []);
            
            // Settings migrations (CRITICAL!)
            $this->info('⚙️  Running settings migrations...');
            $this->call('migrate', [
                '--path' => 'database/settings',
                ...$isProduction ? ['--force' => true] : []
            ]);

            // Fix settings configuration issues
            $this->info('🔧 Fixing settings configuration...');
            $this->call('settings:fix', ['--clear-cache' => true]);
            
            // Verify settings are working
            $this->info('✅ Verifying settings functionality...');
            $this->verifySettings();
            
            // Cache optimization
            if ($isProduction) {
                $this->info('🔄 Optimizing caches...');
                $this->call('config:cache');
                $this->call('route:cache');
                $this->call('view:cache');
            }
            
            $this->info('✅ Deployment completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Deployment failed: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            if ($isProduction) {
                // End maintenance mode
                $this->info('🚀 Disabling maintenance mode...');
                $this->call('up');
            }
        }
        
        return Command::SUCCESS;
    }

    /**
     * Validate environment before deployment
     */
    protected function validateEnvironment(): void
    {
        // Check database connection
        try {
            \DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify settings are working after migration
     */
    protected function verifySettings(): void
    {
        try {
            $settings = \App\Helpers\SettingsHelper::general();
            $this->info('✅ Settings verification: OK - App name: ' . $settings->app_name);
        } catch (\Exception $e) {
            $this->error('❌ Settings verification failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
