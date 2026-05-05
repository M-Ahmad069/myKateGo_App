# FitGo — Laravel backend setup & delivery

## Environment variables (`.env`)

| Variable | Purpose |
|----------|---------|
| `APP_NAME` | e.g. `FitGo` |
| `APP_URL` | Must match your dev URL (e.g. `http://127.0.0.1:8000`) |
| `DB_*` | MySQL connection (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, …) |
| `QUEUE_CONNECTION` | `database` (required for background plan generation) |
| `MAIL_*` | Use `log` driver locally or SMTP/Mailpit |
| `OPENAI_API_KEY` | GPT meal plans (fallback runs if missing or on API errors) |

## Artisan commands

```bash
cd fitgo
composer install
copy .env.example .env   # Windows; use cp on Unix
php artisan key:generate
php artisan migrate
php artisan db:seed --class=UserSeeder   # optional test user
```

Terminal A:

```bash
php artisan serve
```

Terminal B (required for quiz → dashboard queue flow):

```bash
php artisan queue:work
```

## Test user (after seed)

- Email: `test@fitgo.com`
- Password: `password`

## Manual test: quiz → dashboard

1. Open `/`, then `/quiz`.
2. Complete all 12 steps including passwords on step 11.
3. Wait on step 12 until polling succeeds (queue worker must be running).
4. You should land on `/dashboard` with meals, macros, workouts, and chart.

## Files added or touched (summary)

- **Config**: `config/openai.php`, `composer.json`, `.env.example`
- **Database**: migrations (`users` extensions, `diet_plans`, `meal_plans`, `workout_plans`, `progress_logs`), `database/factories/UserFactory.php`, `database/seeders/UserSeeder.php`, `DatabaseSeeder.php`
- **Domain**: `app/Enums/*`, `app/Models/*`, `app/Services/DietPlanService.php`, `app/Services/WorkoutPlanService.php`, `app/Jobs/GenerateUserPlansJob.php`, `app/Mail/WelcomePlanMail.php`
- **HTTP**: `QuizController`, `DashboardController`, `ProgressController`, `routes/web.php`, `routes/auth.php` (register routes removed so only the quiz creates full profiles)
- **Views**: `resources/views/landing.blade.php`, `quiz.blade.php`, `dashboard.blade.php`, `emails/welcome-plan.blade.php` (+ existing Breeze auth views)

Original static HTML files in the repo root (`index.html`, `quiz.html`, `dashboard.html`) were **not** modified; Blade copies live under `resources/views/`.

## Notes

- Laravel Breeze Blade stack is included for login / password reset; **registration via `/register` is removed** because quiz onboarding creates users with all profile fields.
- `resources/views/layouts/app.blade.php` was specified in the product plan as a shared layout; the shipped UI uses **full-page** Blade files preserved from your HTML for pixel parity. You may refactor to `@extends('layouts.app')` later without changing visuals.
