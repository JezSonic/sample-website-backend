<?php

namespace App\Jobs;

use App\Models\UserLoginActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

// Make sure to use your ProfileInformation model
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

// For logging errors/information

class ExportUserDataJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     *
     * @return void
     */
    public function handle(): void {
        //@TODO: Create separate file for loginActivity
        $user = User::where('id', '=', $this->user->id)->first();

        if (!$user) {
            Log::error("ExportUserDataJob: User with ID {$this->user->id} not found. Job failed.");
            return;
        }

        $fileName = 'user_export_' . $this->user->id. '_' . now()->format('Ymd_His') . '.csv';
        $filePath = 'exports/' . $fileName;
        $headers = [
            'User ID',
            'Name',
            'Email',
            'Email Verified At',
            'Created At',
            'Updated At',
            'Avatar source',
            'Is public',
            'Connected GitHub',
            'Connected Google',
        ];

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);
        $row = [
            $user->id,
            $user->name,
            $user->email,
            $user->email_verified_at,
            $user->created_at,
            $user->updated_at,
            $user->profileSettings->avatar_source,
            $user->profileSettings->is_public,
            !($user->githubData() == null),
            !($user->googleData() == null),
        ];
        fputcsv($handle, $row);
        rewind($handle);
        $csvContent = stream_get_contents($handle);
        Storage::disk()->put($filePath, $csvContent);
        fclose($handle);
        $handle = null;

        $file = fopen('php://temp', 'r+');
        fputcsv($file, [
            "IP Address",
            "Location",
            "Browser",
            "Login method",
            "Date & Time"
        ]);

        UserLoginActivity::select(['ip_address', 'location', 'user_agent', 'login_method', 'created_at'])->where('user_id', '=', $this->user->id)
            ->chunk(1000, function ($data) use ($file) {
                foreach ($data as $entry) {
                    fputcsv($file, [
                        $entry->ip_address,
                        $entry->location,
                        $entry->user_agent,
                        $entry->login_method,
                        $entry->created_at,
                    ]);
                }
            });
        $fileName = 'user_export_' . $this->user->id . '_activity_' . now()->format('Ymd_His') . '.csv';
        $filePath = 'exports/' . $fileName;
        rewind($file);
        $csvContent = stream_get_contents($file);
        Storage::disk()->put($filePath, $csvContent);
        fclose($file);

    }
}
