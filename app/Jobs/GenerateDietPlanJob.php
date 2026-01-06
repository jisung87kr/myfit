<?php

namespace App\Jobs;

use App\Models\DietPlan;
use App\Notifications\DietPlanFailedNotification;
use App\Notifications\DietPlanReadyNotification;
use App\Services\DietPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateDietPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DietPlan $dietPlan
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DietPlanService $dietPlanService): void
    {
        try {
            Log::info('Starting diet plan generation', [
                'diet_plan_id' => $this->dietPlan->id,
                'user_id' => $this->dietPlan->user_id,
            ]);

            // Generate the plan with AI
            $dietPlanService->generateWithAI($this->dietPlan);

            Log::info('Diet plan generation completed', [
                'diet_plan_id' => $this->dietPlan->id,
            ]);

            // Send notification to user that plan is ready
            $this->dietPlan->user->notify(new DietPlanReadyNotification($this->dietPlan));

        } catch (\Exception $e) {
            Log::error('Failed to generate diet plan', [
                'diet_plan_id' => $this->dietPlan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to allow retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Diet plan generation job failed permanently', [
            'diet_plan_id' => $this->dietPlan->id,
            'error' => $exception->getMessage(),
        ]);

        // Update plan status to failed
        $this->dietPlan->markAsFailed();

        // Notify user that plan generation failed
        $this->dietPlan->user->notify(new DietPlanFailedNotification($this->dietPlan));
    }
}
