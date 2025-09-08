<?php

namespace App\Domain\User\Jobs;

use App\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        // For now, just log the welcome email since we're not setting up mail configuration
        Log::info('Welcome email would be sent to: ' . $this->user->email, [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_role' => $this->user->role,
        ]);
    }
}