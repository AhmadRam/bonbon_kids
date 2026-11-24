<?php

namespace Webkul\Daftra\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\Daftra\Listeners\OrderListener;
use Webkul\Sales\Repositories\OrderRepository;

class SyncOrderToDaftraJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Do not auto-retry automatically to prevent duplicate remote invoices if an API response timed out.
     */
    public int $tries = 1;

    /**
     * Maximum seconds the job may run before timing out.
     */
    public int $timeout = 180;

    /**
     * Seconds before the unique lock is automatically released.
     */
    public int $uniqueFor = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $orderId) {}

    /**
     * Get the unique ID for the queued job.
     */
    public function uniqueId(): string
    {
        return 'daftra_sync_order_'.$this->orderId;
    }

    /**
     * Execute the job to sync the order invoice to Daftra.
     */
    public function handle(OrderRepository $orderRepository, OrderListener $orderListener): void
    {
        $order = $orderRepository->find($this->orderId);

        if (! $order) {
            Log::warning("Daftra Sync Job: Order #{$this->orderId} not found.");

            return;
        }

        $orderListener->syncOrderToDaftra($order);
    }
}
