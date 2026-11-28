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

class CancelDaftraOrderJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts to delete the Daftra invoice on transient errors.
     */
    public int $tries = 3;

    /**
     * Seconds to wait between retry attempts.
     */
    public int $backoff = 30;

    /**
     * Maximum seconds the job may run before timing out.
     */
    public int $timeout = 120;

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
        return 'daftra_cancel_order_'.$this->orderId;
    }

    /**
     * Execute the job to cancel/delete the order invoice in Daftra.
     */
    public function handle(OrderRepository $orderRepository, OrderListener $orderListener): void
    {
        $order = $orderRepository->find($this->orderId);

        if (! $order) {
            Log::warning("Daftra Cancel Job: Order #{$this->orderId} not found.");

            return;
        }

        $orderListener->cancelOrderInDaftra($order);
    }
}
