<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CodeGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * user
     *
     * @var  App\Models\User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  App\Models\User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

}
