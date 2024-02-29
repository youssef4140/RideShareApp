<?php

namespace App\Events;

use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripCanceledByDriver
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

   
    public $trip;
    private $id;
    /**
     * Create a new event instance.
     */
    public function __construct(Trip $trip, $id)
    {
        $this->trip = $trip;
        $this->id = $id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new channel('passenger_'.$this->id),
        ];
    }
}
