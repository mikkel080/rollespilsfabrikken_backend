<?php

namespace App\Jobs\Test;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Calendar;
use Illuminate\Support\Facades\Log;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $title = "Job test";
    public string $description = "Job test body";

    /**
     * Create a new job instance.
     *
     * @param $title
     * @param $description
     */
    public function __construct($title, $description)
    {
        Log::debug("Constructing");
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("Handling");
        (new Calendar())->create([
            'title' => $this->title,
            'description' => $this->description,
            'obj_id' => 90000
        ]);
    }
}
