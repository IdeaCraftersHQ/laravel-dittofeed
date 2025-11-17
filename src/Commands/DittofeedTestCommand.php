<?php

namespace Dittofeed\Laravel\Commands;

use Dittofeed\Laravel\Facades\Dittofeed;
use Illuminate\Console\Command;

class DittofeedTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dittofeed:test {--user-id= : User ID to use for testing}';

    /**
     * The console command description.
     */
    protected $description = 'Test your Dittofeed integration by sending test events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Dittofeed integration...');
        $this->newLine();

        $userId = $this->option('user-id') ?? 'test-user-' . uniqid();

        try {
            // Test identify
            $this->info('1. Testing identify()...');
            Dittofeed::identify($userId, [
                'email' => 'test@example.com',
                'name' => 'Test User',
                'plan' => 'test',
            ]);
            $this->line('   ✓ Identify successful');

            // Test track
            $this->info('2. Testing track()...');
            Dittofeed::track('Test Event', [
                'category' => 'testing',
                'source' => 'cli',
            ], $userId);
            $this->line('   ✓ Track successful');

            // Test page
            $this->info('3. Testing page()...');
            Dittofeed::page('Test Page', [
                'url' => 'https://example.com/test',
                'title' => 'Test Page',
            ], $userId);
            $this->line('   ✓ Page successful');

            // Test group
            $this->info('4. Testing group()...');
            Dittofeed::group('test-group-' . uniqid(), [
                'name' => 'Test Group',
                'plan' => 'enterprise',
            ], $userId);
            $this->line('   ✓ Group successful');

            $this->newLine();
            $this->info('✓ All tests passed! Your Dittofeed integration is working correctly.');
            $this->line("Test User ID: {$userId}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ Test failed: ' . $e->getMessage());

            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
