<?php

namespace Dittofeed\Laravel\Commands;

use Dittofeed\Laravel\Facades\Dittofeed;
use Illuminate\Console\Command;

class DittofeedFlushCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dittofeed:flush';

    /**
     * The console command description.
     */
    protected $description = 'Flush any queued Dittofeed events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Flushing Dittofeed event queue...');

            Dittofeed::flush();

            $this->info('✓ Event queue flushed successfully.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Failed to flush event queue: ' . $e->getMessage());

            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
