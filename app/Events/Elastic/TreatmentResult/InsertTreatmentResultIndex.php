<?php

namespace App\Events\Elastic\TreatmentResult;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InsertTreatmentResultIndex
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $record;
    public $modelName;
    public function __construct($record, $modelName)
    {
        $this->record = $record;
        $this->modelName = $modelName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('elastic-treatment-result-insert-index'),
        ];
    }
}
