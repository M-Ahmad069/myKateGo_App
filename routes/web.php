<?php

use App\Http\Controllers\AI\ChatController as AiChatController;
use App\Http\Controllers\AI\GroceryController as AiGroceryController;
use App\Http\Controllers\AI\ProgressAnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FitGoCoachController;
use App\Http\Controllers\FitGoProfileController;
use App\Http\Controllers\GroceryPageController;
use App\Http\Controllers\MealPlansPageController;
use App\Http\Controllers\WorkoutsPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProgressPageController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::get('/quiz', [QuizController::class, 'show'])->name('quiz');
Route::post('/quiz/submit', [QuizController::class, 'submit'])->name('quiz.submit');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/app/profile', [FitGoProfileController::class, 'show'])->name('fitgo.profile');
    Route::patch('/app/profile', [FitGoProfileController::class, 'update'])->name('fitgo.profile.update');
    Route::get('/app/progress', [ProgressPageController::class, 'index'])->name('fitgo.progress');
    Route::get('/app/coach', [FitGoCoachController::class, 'show'])->name('fitgo.coach');
    Route::post('/app/coach/message', [FitGoCoachController::class, 'respond'])->name('fitgo.coach.message');

    Route::get('/app/meals', [MealPlansPageController::class, 'index'])->name('fitgo.meals');
    Route::get('/app/grocery', [GroceryPageController::class, 'index'])->name('fitgo.grocery');
    Route::post('/app/grocery/toggle', [GroceryPageController::class, 'toggle'])->name('fitgo.grocery.toggle');
    Route::get('/app/workouts', [WorkoutsPageController::class, 'index'])->name('fitgo.workouts');

    Route::get('/plan-status', function () {
        $user = auth()->user();
        $ready = $user && $user->dietPlan && $user->mealPlans()->exists();

        return response()->json([
            'ready' => (bool) $ready,
            'plan_status' => $user?->plan_status,
        ]);
    })->name('plan.status');

    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/chat', [AiChatController::class, 'send'])->name('chat');
        Route::get('/chat/history', [AiChatController::class, 'history'])->name('chat.history');
        Route::get('/progress', [ProgressAnalysisController::class, 'analyse'])->name('progress');
        Route::get('/grocery', [AiGroceryController::class, 'generate'])->name('grocery');
    });

    Route::post('/progress', [ProgressController::class, 'store'])->name('progress.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
