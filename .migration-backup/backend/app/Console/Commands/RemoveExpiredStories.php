<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Story;
use App\Services\StoryService\StoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RemoveExpiredStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:expired:stories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remove expired stories';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(StoryService $service): int
    {
        $failed = false;
        Story::query()->expired()->select(['id', 'shop_id'])->chunkById(100, function ($stories) use ($service, &$failed): void {
            foreach ($stories as $story) {
                try {
                    $service->delete([$story->id], (int) $story->shop_id);
                } catch (Throwable $e) {
                    $failed = true;
                    Log::error('Story expiry cleanup failed', ['story_id' => $story->id, 'message' => $e->getMessage()]);
                }
            }
        });

        return $failed ? 1 : 0;
    }
}
