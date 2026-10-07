<?php

namespace App\Events\Order;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SendDeliveryManLocationByOrder
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param array $orderIds
     * @param string $language
     * @param int|null $actorId Authenticated driver ID; null is retained for
     *                          constructor compatibility and fails closed.
     */
    public function __construct(
        public array $orderIds,
        public string $language = 'en',
        public ?int $actorId = null
    )
    {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
