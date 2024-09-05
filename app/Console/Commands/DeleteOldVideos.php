<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DeleteOldVideos extends Command
{
    protected $signature = 'videos:delete-old';
    protected $description = 'Delete videos older than 72 hours';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $videos = Video::where('created_at', '<', Carbon::now()->subHours(72))->get();

        foreach ($videos as $video) {
            // Delete the video file from storage
            Storage::disk('public')->delete($video->path);

            // Delete the record from the database
            $video->delete();
        }

        $this->info('Old videos deleted successfully.');
    }
}
