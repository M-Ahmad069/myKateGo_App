<?php

namespace App\Jobs;

use App\Mail\WelcomePlanMail;
use App\Models\User;
use App\Services\DietPlanService;
use App\Services\WorkoutPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateUserPlansJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Allow long OpenAI generation inside the worker.
     */
    public int $timeout = 360;

    public function __construct(public int $userId) {}

    public function handle(DietPlanService $dietPlanService, WorkoutPlanService $workoutPlanService): void
    {
        $user = User::query()->findOrFail($this->userId);
        $user->update(['plan_status' => 'generating']);

        try {
            try {
                $dietPlanService->generate($user);
            } catch (\Throwable $e) {
                Log::error('Diet plan generation failed', ['user' => $this->userId, 'error' => $e->getMessage()]);
                throw $e;
            }

            try {
                $workoutPlanService->generate($user);
            } catch (\Throwable $e) {
                Log::error('Workout plan generation failed', ['user' => $this->userId, 'error' => $e->getMessage()]);
                throw $e;
            }

            $user->refresh();
            $user->load('dietPlan');

            if (config('fitgo.send_welcome_email')) {
                try {
                    Mail::to($user->email)->send(new WelcomePlanMail($user));
                } catch (\Throwable $e) {
                    Log::warning('Welcome email failed', ['user' => $this->userId, 'error' => $e->getMessage()]);
                }
            }

            $user->update(['plan_status' => 'ready']);
        } catch (\Throwable $e) {
            $user->update(['plan_status' => 'failed']);
            throw $e;
        }
    }
}
