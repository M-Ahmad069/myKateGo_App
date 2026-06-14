<?php

return [
    /*
    | Meal plan generation: `engine` (rule-based) or `openai` (default).
    */
    'meal_plan_driver' => env('FITGO_MEAL_DRIVER', 'engine'),

    /*
    | Workout plan generation: `engine` (rule-based templates) or `openai`.
    */
    'workout_plan_driver' => env('FITGO_WORKOUT_DRIVER', 'engine'),

    /*
    | Optional: set true to send WelcomePlanMail after GenerateUserPlansJob (default off).
    */
    'send_welcome_email' => env('FITGO_SEND_WELCOME_EMAIL', false),
];
