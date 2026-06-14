# FitGo — start in 3 steps (final project)

## Every time you work

1. **Laragon** → **Start All**
2. Terminal:
   ```cmd
   cd /d "e:\MyKateGO App\fitgo"
   php artisan serve
   ```
3. Browser: **http://127.0.0.1:8000**

## First time only (new PC)

```cmd
cd /d "e:\MyKateGO App\fitgo"
composer install
copy .env.example .env
php artisan key:generate
```

Create MySQL database **`fitgo`** in Laragon → Database, then:

```cmd
php artisan migrate
```

## What the app does (for viva)

1. **Quiz** (`/quiz`) — creates a **user** in the database and builds **diet + workout** plans using **rules** (calories/macros + templates).
2. **Dashboard** — shows today’s meals and weekly workouts from the database.
3. **Login** (`/login`) — same email/password as on the quiz.

No OpenAI key required. Plans are calculated in PHP (Laravel services).

## If quiz hangs on “Generating…”

- Laragon **MySQL** must be running.
- `.env` should have `QUEUE_CONNECTION=sync` and `DB_DATABASE=fitgo`.
