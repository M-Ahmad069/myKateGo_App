<?php

namespace App\Http\Controllers;

use App\Enums\ActivityLevel;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Enums\WorkoutPreference;
use App\Jobs\GenerateUserPlansJob;
use App\Models\User;
use App\Services\QuizProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
    public function show()
    {
        return view('quiz');
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gender' => ['required', Rule::enum(Gender::class)],
            'goal' => ['required', Rule::enum(Goal::class)],
            'age' => ['required', 'integer', 'min:16', 'max:80'],
            'height' => ['required', 'numeric', 'min:1'],
            'heightSystem' => ['nullable', 'in:cm,ft'],
            'weight' => ['required', 'numeric', 'min:30'],
            'targetWeight' => ['required', 'numeric', 'min:30'],
            'weightSystem' => ['nullable', 'in:kg,lbs'],
            'activity' => ['required', Rule::enum(ActivityLevel::class)],
            'workout' => ['required', Rule::enum(WorkoutPreference::class)],
            'diet' => ['nullable', 'array'],
            'diet.*' => ['string', 'in:none,vegetarian,dairy_free,gluten_free'],
            'genderSpecific' => [
                'required',
                'string',
                Rule::in(match ((string) $request->input('gender')) {
                    Gender::Male->value => ['beginner', 'some_exp', 'intermediate', 'advanced'],
                    Gender::Female->value => ['none', 'pcos', 'thyroid', 'menopause'],
                    default => ['__invalid__'],
                }),
            ],
            'trainingFocus' => [
                Rule::requiredIf(fn () => (string) $request->input('gender') === Gender::Male->value),
                'nullable',
                'string',
                Rule::in(['lose_fat', 'build_muscle', 'recomp']),
            ],
            'recoveryStress' => [
                Rule::requiredIf(fn () => (string) $request->input('gender') === Gender::Male->value),
                'nullable',
                'string',
                Rule::in(['low', 'med', 'high']),
            ],
            'cycleRegularity' => [
                Rule::requiredIf(fn () => (string) $request->input('gender') === Gender::Female->value),
                'nullable',
                'string',
                Rule::in(['regular', 'irregular', 'na']),
            ],
            'energyLevel' => [
                Rule::requiredIf(fn () => (string) $request->input('gender') === Gender::Female->value),
                'nullable',
                'string',
                Rule::in(['low', 'moderate', 'high']),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $heightCm = $this->normalizeHeight($request);
        $weightKg = $this->normalizeWeight($request);
        $targetKg = (float) $validated['targetWeight'];

        $diet = $validated['diet'] ?? [];
        if ($diet === []) {
            $diet = ['none'];
        }

        // JSON quiz submit may leave enums as strings; normalize before services/models.
        $validated['gender'] = $this->toEnum($validated['gender'], Gender::class);
        $validated['goal'] = $this->toEnum($validated['goal'], Goal::class);
        $validated['activity'] = $this->toEnum($validated['activity'], ActivityLevel::class);
        $validated['workout'] = $this->toEnum($validated['workout'], WorkoutPreference::class);

        $profile = app(QuizProfileService::class)->buildFromValidated($validated);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'gender' => $validated['gender'],
            'age' => $validated['age'],
            'height_cm' => $heightCm,
            'weight_kg' => $weightKg,
            'target_weight_kg' => $targetKg,
            'activity_level' => $validated['activity'],
            'workout_preference' => $validated['workout'],
            'diet_restrictions' => $diet,
            'gender_specific_data' => $validated['genderSpecific'] ?? null,
            'goal' => $validated['goal'],
            'quiz_profile' => $profile['quiz_profile'],
            'plan_segment' => $profile['plan_segment'],
            'coaching_tags' => $profile['coaching_tags'],
            'plan_status' => 'generating',
        ]);

        Auth::login($user);

        GenerateUserPlansJob::dispatch($user->id);

        return response()->json([
            'status' => 'queued',
            'ready' => false,
            'message' => 'Your plan is being generated.',
        ], 202);
    }

    /**
     * @template T of \BackedEnum
     *
     * @param  T|scalar  $value
     * @param  class-string<T>  $enumClass
     * @return T
     */
    protected function toEnum(mixed $value, string $enumClass): \BackedEnum
    {
        if ($value instanceof $enumClass) {
            return $value;
        }

        return $enumClass::from((string) $value);
    }

    protected function normalizeHeight(Request $request): float
    {
        $h = (float) $request->input('height');
        $sys = $request->input('heightSystem', 'cm');

        if ($sys === 'ft') {
            $feet = (int) floor($h);
            $inches = (int) round(($h - $feet) * 100);
            if ($inches > 11) {
                $inches = (int) round(($h - $feet) * 10);
            }
            $inches = max(0, min(11, $inches));

            return round(($feet * 12 + $inches) * 2.54, 2);
        }

        return round($h, 2);
    }

    protected function normalizeWeight(Request $request): float
    {
        $w = (float) $request->input('weight');
        if ($request->input('weightSystem') === 'lbs') {
            return round($w * 0.45359237, 2);
        }

        return round($w, 2);
    }
}
