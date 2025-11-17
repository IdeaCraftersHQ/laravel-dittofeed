<?php

namespace Ideacrafters\Dittofeed\Jobs;

use Ideacrafters\Dittofeed\DittofeedClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDittofeedEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $type,
        protected array $data
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $config = config('dittofeed');
            $client = new DittofeedClient($config);

            match ($this->type) {
                'identify' => $client->identify($this->data),
                'track' => $client->track($this->data),
                'page' => $client->page($this->data),
                'screen' => $client->screen($this->data),
                'group' => $client->group($this->data),
                default => throw new \InvalidArgumentException("Unknown event type: {$this->type}"),
            };
        } catch (\Exception $e) {
            Log::error('Failed to send Dittofeed event', [
                'type' => $this->type,
                'data' => $this->data,
                'error' => $e->getMessage(),
                'attempts' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Dittofeed event job failed permanently', [
            'type' => $this->type,
            'data' => $this->data,
            'error' => $exception->getMessage(),
        ]);
    }
}
