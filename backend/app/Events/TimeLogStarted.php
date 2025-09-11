<?php

namespace App\Events;

use App\Domain\Task\Models\TimeLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimeLogStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TimeLog $timeLog;

    /**
     * Create a new event instance.
     */
    public function __construct(TimeLog $timeLog)
    {
        $this->timeLog = $timeLog->load(['task', 'user']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('time-tracking'),
            new PrivateChannel('user.' . $this->timeLog->user_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'time_log' => [
                'id' => $this->timeLog->id,
                'task_id' => $this->timeLog->task_id,
                'user_id' => $this->timeLog->user_id,
                'start_time' => $this->timeLog->start_time,
                'activity_type' => $this->timeLog->activity_type,
                'is_active' => $this->timeLog->is_active,
                'task' => $this->timeLog->task ? [
                    'id' => $this->timeLog->task->id,
                    'name' => $this->timeLog->task->name,
                ] : null,
                'user' => $this->timeLog->user ? [
                    'id' => $this->timeLog->user->id,
                    'name' => $this->timeLog->user->name,
                ] : null,
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'time-log.started';
    }
}
