<?php

namespace App\Jobs;

use App\Models\UserDataExports;
use App\Models\UserLoginActivity;
use App\Utils\Enums\UserDataExportStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

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
        $user = User::find($this->user->id); // Use find for direct retrieval by ID
        if (!$user) {
            Log::error("ExportUserDataJob: User with ID {$this->user->id} not found. Job failed.");
            return;
        }

        UserDataExports::where('user_id', '=', $this->user->id)->update([
            'status' => UserDataExportStatus::PROCESSING->value,
            'valid_until' => now()->addDays(),
        ]);
        $exportDirectory = 'exports/' . $this->user->id;

        if (Storage::exists($exportDirectory)) {
            Storage::deleteDirectory($exportDirectory);
        }

        $profileFileName = 'user_profile_' . $user->id . '_' . now()->format('Ymd_His') . '.csv';
        $profileFilePath = $exportDirectory . '/' . $profileFileName;
        $profileHeaders = [
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
        fputcsv($handle, $profileHeaders);
        $row = [
            $user->id,
            $user->name,
            $user->email,
            $user->email_verified_at,
            $user->created_at,
            $user->updated_at,
            $user->profileSettings->avatar_source ?? null, // Use null coalescing operator
            $user->profileSettings->is_public ?? null,
            !($user->githubData()->first() == null), // Check if a record exists
            !($user->googleData()->first() == null), // Check if a record exists
        ];
        fputcsv($handle, $row);
        rewind($handle);
        $csvContent = stream_get_contents($handle);
        Storage::put($profileFilePath, $csvContent);
        fclose($handle);

        $activityFileName = 'user_activity_' . $user->id . '_' . now()->format('Ymd_His') . '.csv';
        $activityFilePath = $exportDirectory . '/' . $activityFileName;
        $activityHeaders = [
            "IP Address",
            "Location",
            "Browser",
            "Login method",
            "Date & Time"
        ];

        $file = fopen('php://temp', 'r+');
        fputcsv($file, $activityHeaders);

        UserLoginActivity::select(['ip_address', 'location', 'user_agent', 'login_method', 'created_at'])
            ->where('user_id', '=', $this->user->id)
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
        rewind($file);
        $csvContent = stream_get_contents($file);
        Storage::put($activityFilePath, $csvContent);
        fclose($file);

        $sourcePath = Storage::path($exportDirectory);
        $outputPath = Storage::path($exportDirectory . '/data.zip');
        $zip = new ZipArchive;
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = File::allFiles($sourcePath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'csv') { // Only add CSV files
                    $filePath = $file->getRealPath();
                    $relativePath = str_replace($sourcePath . DIRECTORY_SEPARATOR, '', $filePath);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            Storage::delete($profileFilePath);
            Storage::delete($activityFilePath);
            UserDataExports::where('user_id', '=', $this->user->id)->update([
                'status' => UserDataExportStatus::COMPLETED->value,
                'valid_until' => now()->addDays(),
            ]);
            Log::info("ExportUserDataJob: Export completed for user ID: {$user->id}");

        } else {
            Log::error("ExportUserDataJob: Failed to create zip archive for user ID: {$user->id}. ZipArchive error code: {$zip->status}");
            UserDataExports::where('user_id', '=', $this->user->id)->update([
                'status' => UserDataExportStatus::FAILED->value,
                'valid_until' => now()->addDays(),
            ]);
        }

        DeleteExpiredUserDataJob::dispatch($this->user)->delay(now()->addDays());
    }
}
