<?php

namespace Dittofeed\Laravel\Commands;

use Illuminate\Console\Command;

class DittofeedStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dittofeed:stats';

    /**
     * The console command description.
     */
    protected $description = 'Display Dittofeed configuration and status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $config = config('dittofeed');

        $this->info('Dittofeed Configuration');
        $this->newLine();

        // Connection
        $this->line('<fg=cyan>Connection</>');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', $config['host']],
                ['Write Key', $this->maskKey($config['write_key'] ?? null)],
                ['Admin Key', $this->maskKey($config['admin_key'] ?? null)],
                ['Workspace ID', $config['workspace_id'] ?? 'Not set'],
                ['Timeout', $config['timeout'] . 's'],
                ['SSL Verification', $config['verify_ssl'] ? 'Enabled' : 'Disabled'],
            ]
        );

        $this->newLine();

        // Queue Configuration
        $this->line('<fg=cyan>Queue Configuration</>');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Queue Enabled', $config['queue']['enabled'] ? 'Yes' : 'No'],
                ['Queue Name', $config['queue']['queue']],
                ['Connection', $config['queue']['connection'] ?? 'Default'],
            ]
        );

        $this->newLine();

        // Auto Tracking
        $this->line('<fg=cyan>Auto Tracking</>');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Enabled', $config['auto_track']['enabled'] ? 'Yes' : 'No'],
                ['Page Views', $config['auto_track']['page_views'] ? 'Yes' : 'No'],
                ['Auth Events', $config['auto_track']['auth_events'] ? 'Yes' : 'No'],
                ['Model Events', $config['auto_track']['model_events'] ? 'Yes' : 'No'],
            ]
        );

        $this->newLine();

        // Context
        $this->line('<fg=cyan>Context Enrichment</>');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Enabled', $config['context']['enabled'] ? 'Yes' : 'No'],
                ['IP Address', $config['context']['ip'] ? 'Yes' : 'No'],
                ['User Agent', $config['context']['user_agent'] ? 'Yes' : 'No'],
                ['Timezone', $config['context']['timezone'] ? 'Yes' : 'No'],
                ['Locale', $config['context']['locale'] ? 'Yes' : 'No'],
            ]
        );

        $this->newLine();

        // Status
        $this->line('<fg=cyan>Status</>');
        $this->table(
            ['Check', 'Status'],
            [
                ['Write Key Configured', !empty($config['write_key']) ? '✓ Yes' : '✗ No'],
                ['Admin Key Configured', !empty($config['admin_key']) ? '✓ Yes' : '✗ No'],
                ['Debug Mode', $config['debug'] ? '⚠ Enabled' : '✓ Disabled'],
                ['Testing Mode', $config['testing'] ? '⚠ Enabled' : '✓ Disabled'],
            ]
        );

        if (empty($config['write_key'])) {
            $this->newLine();
            $this->warn('⚠ Write key is not configured. Please set DITTOFEED_WRITE_KEY in your .env file.');
        }

        if ($config['debug']) {
            $this->newLine();
            $this->warn('⚠ Debug mode is enabled. This may log sensitive data.');
        }

        if ($config['testing']) {
            $this->newLine();
            $this->info('ℹ Testing mode is enabled. Events will not be sent to Dittofeed.');
        }

        return self::SUCCESS;
    }

    /**
     * Mask an API key for display.
     */
    protected function maskKey(?string $key): string
    {
        if (empty($key)) {
            return 'Not set';
        }

        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }

        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
    }
}
