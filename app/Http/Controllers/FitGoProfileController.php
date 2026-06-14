<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Enums\Goal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FitGoProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load('dietPlan');

        $latestProgress = $user->progressLogs()
            ->orderByDesc('logged_date')
            ->first();

        return view('fitgo.profile', [
            'user' => $user,
            'summary' => $this->profileSummary($user),
            'latestProgress' => $latestProgress,
            'mealCount' => $user->mealPlans()->count(),
            'workoutCount' => $user->workoutPlans()->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return redirect()->route('fitgo.profile')->with('status', 'profile-updated');
    }

    /**
     * @return array<string, mixed>
     */
    protected function profileSummary(User $user): array
    {
        $qp = $user->quiz_profile ?? [];
        $shared = $qp['shared'] ?? [];
        $male = $qp['male_track'] ?? null;
        $female = $qp['female_track'] ?? null;

        $goalLabel = match ($user->goal) {
            Goal::LoseWeight => 'Lose weight',
            Goal::BuildMuscle => 'Build muscle',
            Goal::GetFit => 'Get fit',
            Goal::Maintain => 'Maintain & tone',
            default => '—',
        };

        $activityLabel = match ($user->activity_level->value ?? '') {
            'sedentary' => 'Sedentary',
            'light' => 'Lightly active',
            'moderate' => 'Moderately active',
            'active' => 'Very active',
            'very_active' => 'Extra active',
            default => ucfirst((string) ($user->activity_level->value ?? '—')),
        };

        $workoutLabel = match ($user->workout_preference->value ?? '') {
            'home' => 'Home workouts',
            'gym' => 'Gym workouts',
            'both' => 'Home & gym',
            'none' => 'Nutrition focus only',
            default => '—',
        };

        $planStatusLabel = match ($user->plan_status) {
            'ready' => 'Plan ready',
            'generating' => 'Generating plan…',
            'failed' => 'Plan failed — retake quiz',
            default => 'Not started',
        };

        $planStatusTone = match ($user->plan_status) {
            'ready' => 'success',
            'generating' => 'pending',
            'failed' => 'danger',
            default => 'muted',
        };

        $dietLabels = collect($user->diet_restrictions ?? ['none'])
            ->map(fn (string $d) => match ($d) {
                'none' => 'No restrictions',
                'vegetarian' => 'Vegetarian',
                'dairy_free' => 'Dairy-free',
                'gluten_free' => 'Gluten-free',
                default => ucfirst(str_replace('_', ' ', $d)),
            })
            ->unique()
            ->values()
            ->all();

        $focusLines = [];
        if ($user->gender === Gender::Male) {
            $exp = (string) ($male['experience'] ?? $user->gender_specific_data ?? '');
            $focusLines[] = ['label' => 'Training level', 'value' => match ($exp) {
                'beginner' => 'Beginner',
                'some_exp' => 'Some experience',
                'intermediate' => 'Intermediate',
                'advanced' => 'Advanced',
                default => $exp !== '' ? ucfirst(str_replace('_', ' ', $exp)) : '—',
            }];
            $tf = (string) ($male['training_focus'] ?? '');
            $focusLines[] = ['label' => 'Training focus', 'value' => match ($tf) {
                'lose_fat' => 'Fat loss',
                'build_muscle' => 'Build muscle',
                'recomp' => 'Recomposition',
                default => $tf !== '' ? ucfirst(str_replace('_', ' ', $tf)) : '—',
            }];
            $rec = (string) ($male['recovery_stress'] ?? '');
            $focusLines[] = ['label' => 'Recovery / stress', 'value' => match ($rec) {
                'low' => 'Low',
                'med' => 'Moderate',
                'high' => 'High',
                default => $rec !== '' ? ucfirst($rec) : '—',
            }];
        } else {
            $horm = (string) ($female['hormonal_context'] ?? $user->gender_specific_data ?? '');
            $focusLines[] = ['label' => 'Hormonal context', 'value' => match ($horm) {
                'none' => 'None selected',
                'pcos' => 'PCOS-aware plan',
                'thyroid' => 'Thyroid-aware plan',
                'menopause' => 'Menopause-aware plan',
                default => $horm !== '' ? ucfirst($horm) : '—',
            }];
            $cyc = (string) ($female['cycle_regularity'] ?? '');
            $focusLines[] = ['label' => 'Cycle', 'value' => match ($cyc) {
                'regular' => 'Regular',
                'irregular' => 'Irregular',
                'na' => 'Not applicable',
                default => $cyc !== '' ? ucfirst($cyc) : '—',
            }];
            $en = (string) ($female['energy_level'] ?? '');
            $focusLines[] = ['label' => 'Energy', 'value' => match ($en) {
                'low' => 'Low',
                'moderate' => 'Moderate',
                'high' => 'High',
                default => $en !== '' ? ucfirst($en) : '—',
            }];
        }

        $coachingLabels = collect($user->coaching_tags ?? [])
            ->map(fn (string $tag) => $this->friendlyCoachingTag($tag))
            ->values()
            ->all();

        $weightDelta = round((float) $user->weight_kg - (float) $user->target_weight_kg, 1);

        return [
            'goalLabel' => $goalLabel,
            'activityLabel' => $activityLabel,
            'workoutLabel' => $workoutLabel,
            'planStatusLabel' => $planStatusLabel,
            'planStatusTone' => $planStatusTone,
            'dietLabels' => $dietLabels,
            'focusLines' => $focusLines,
            'coachingLabels' => $coachingLabels,
            'weightDelta' => $weightDelta,
            'weightDeltaText' => $weightDelta > 0
                ? number_format($weightDelta, 1).' kg to lose'
                : ($weightDelta < 0
                    ? number_format(abs($weightDelta), 1).' kg to gain'
                    : 'At target weight'),
        ];
    }

    protected function friendlyCoachingTag(string $tag): string
    {
        return match ($tag) {
            'male' => 'Male programme',
            'female' => 'Female programme',
            'male_exp_beginner' => 'Beginner track',
            'male_exp_some_exp' => 'Some gym experience',
            'male_exp_intermediate' => 'Intermediate lifter',
            'male_exp_advanced' => 'Advanced lifter',
            'focus_lose_fat' => 'Fat-loss focus',
            'focus_build_muscle' => 'Muscle-building focus',
            'focus_recomp' => 'Recomposition',
            'recovery_low' => 'Good recovery',
            'recovery_med' => 'Moderate stress',
            'recovery_high' => 'High stress / recovery focus',
            'female_horm_pcos' => 'PCOS support',
            'female_horm_thyroid' => 'Thyroid support',
            'female_horm_menopause' => 'Menopause support',
            'cycle_regular' => 'Regular cycle',
            'cycle_irregular' => 'Irregular cycle',
            'energy_low' => 'Low energy',
            'energy_moderate' => 'Moderate energy',
            'energy_high' => 'High energy',
            default => ucwords(str_replace('_', ' ', $tag)),
        };
    }
}
