<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use File;

class DeleteOldVideos extends Command
{
    // The name and signature of the console command.
    protected $signature = 'videos:delete-old';

    // The console command description.
    protected $description = 'Delete videos older than 72 hours from the storage folder';

    // Create a new command instance.
    public function __construct()
    {
        parent::__construct();
    }

    // Execute the console command.
    public function handle()
    {
        // Path to the storage folder where videos are stored
        $videosPath = storage_path('app/public/videos');

        // Get all video files from the folder
        $files = File::allFiles($videosPath);

        foreach ($files as $file) {
            // Get the file's last modified time
            $lastModified = Carbon::createFromTimestamp(File::lastModified($file));

            // If the file is older than 72 hours, delete it
            if ($lastModified->lt(Carbon::now()->subHours(72))) {
                Storage::disk('public')->delete('videos/' . $file->getFilename());
                $this->info('Deleted: ' . $file->getFilename());
            }
        }

        $this->info('Old videos deleted successfully.');
    }
}
