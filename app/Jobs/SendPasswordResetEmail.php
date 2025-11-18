<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPasswordResetEmail implements ShouldQueue
{
    use Queueable;

    public $user;
    public $token;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new ResetPasswordNotification($this->token));
    }
}
