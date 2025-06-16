<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserDataExports;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class DeleteExpiredUserDataJob implements ShouldQueue {
    use Queueable, Dispatchable;

    /**
     * The ID of the user to export.
     *
     * @var User
     */
    protected User $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        Storage::deleteDirectory('exports/' . $this->user->id);
        UserDataExports::where('user_id', '=', $this->user->id)->delete();
    }
}
