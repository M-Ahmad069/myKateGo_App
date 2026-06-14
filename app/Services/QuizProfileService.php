<?php

namespace App\Services;

use App\Enums\Gender;

class QuizProfileService
{
    /**
     * @param  array<string, mixed>  $validated  Output of QuizController validation (enums resolved)
     * @return array{quiz_profile: array<string, mixed>, plan_segment: string, coaching_tags: array<int, string>}
     */
    public function buildFromValidated(array $validated): array
    {
        $gender = $validated['gender'];
        if (is_string($gender)) {
            $gender = Gender::from($gender);
        }

        $shared = [
            'goal' => $validated['goal']->value ?? (string) $validated['goal'],
            'activity' => $validated['activity']->value ?? (string) $validated['activity'],
            'workout' => $validated['workout']->value ?? (string) $validated['workout'],
            'diet' => $validated['diet'] ?? [],
            'systems' => [
                'height' => $validated['heightSystem'] ?? 'cm',
                'weight' => $validated['weightSystem'] ?? 'kg',
            ],
        ];

        $maleTrack = null;
        $femaleTrack = null;
        $tags = [];

        if ($gender === Gender::Male) {
            $experience = (string) ($validated['genderSpecific'] ?? '');
            $focus = (string) ($validated['trainingFocus'] ?? '');
            $recovery = (string) ($validated['recoveryStress'] ?? '');
            $maleTrack = [
                'experience' => $experience,
                'training_focus' => $focus,
                'recovery_stress' => $recovery,
            ];
            $tags = array_merge($tags, [
                'male',
                'male_exp_'.$experience,
                'focus_'.$focus,
                'recovery_'.$recovery,
            ]);
        }

        if ($gender === Gender::Female) {
            $hormonal = (string) ($validated['genderSpecific'] ?? '');
            $cycle = (string) ($validated['cycleRegularity'] ?? '');
            $energy = (string) ($validated['energyLevel'] ?? '');
            $femaleTrack = [
                'hormonal_context' => $hormonal,
                'cycle_regularity' => $cycle,
                'energy_level' => $energy,
            ];
            $tags = array_merge($tags, [
                'female',
                'female_horm_'.$hormonal,
                'cycle_'.$cycle,
                'energy_'.$energy,
            ]);
        }

        $quizProfile = [
            'version' => 1,
            'shared' => $shared,
            'male_track' => $maleTrack,
            'female_track' => $femaleTrack,
        ];

        $segment = $this->buildPlanSegment($gender, $validated, $tags);

        return [
            'quiz_profile' => $quizProfile,
            'plan_segment' => $segment,
            'coaching_tags' => array_values(array_unique(array_filter($tags))),
        ];
    }

    /**
     * @param  array<int, string>  $tags
     */
    protected function buildPlanSegment(Gender $gender, array $validated, array $tags): string
    {
        if ($gender === Gender::Male) {
            $exp = (string) ($validated['genderSpecific'] ?? 'unknown');
            $focus = str_replace('_', '', (string) ($validated['trainingFocus'] ?? 'x'));
            $rec = (string) ($validated['recoveryStress'] ?? 'x');

            return 'male_'.$exp.'_'.$focus.'_'.$rec;
        }

        $h = (string) ($validated['genderSpecific'] ?? 'unknown');
        $cyc = str_replace('_', '', (string) ($validated['cycleRegularity'] ?? 'x'));
        $en = (string) ($validated['energyLevel'] ?? 'x');

        return 'female_'.$h.'_'.$cyc.'_'.$en;
    }
}
