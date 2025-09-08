# Bumpa Loyalty — Achievements Microservice

## What this project is

A small Laravel-based API microservice that tracks loyalty achievements and badges, processes purchases, evaluates achievement rules, and issues cashback when certain achievements are unlocked.

Key features
- Achievement and badge models with flexible JSON rules/criteria
- Event-driven flow: `ProcessPurchase` job → `AchievementUnlocked` / `BadgeUnlocked` events → listeners
- Payment gateway abstraction (`PaymentGateway`) with a `PaystackGateway` implementation
- Authentication via Laravel Sanctum (token-based)
- Database queue driver for background jobs (default)
- Tests written with Pest (unit and integration)

## Design choices (short)
- Rules/criteria are stored as JSON on achievements/badges to allow adding new rule types without schema changes.
- Event-driven: unlocking an achievement fires events so side-effects (cashback, notifications) are decoupled.
- Payment gateway interface allows swapping mock/gateway implementations in tests and production.
- Database queue (QUEUE_CONNECTION=database) is used for simplicity in local/dev. The project includes a sample `docker-compose.yml` with RabbitMQ for production-like setups (optional).

## Quick setup (local macOS / Linux)
Prerequisites
- PHP 8.1+ with extensions required by Laravel
- Composer
- SQLite (default) or MySQL/Postgres
- (Optional) Docker + Docker Compose

1. Clone and install

```bash
composer install
cp .env.example .env
php artisan key:generate
```

2. Configure environment
- Set DB connection in `.env` (default `sqlite` works out-of-the-box). If using sqlite, create the file or ensure `database/database.sqlite` exists.
- Set `QUEUE_CONNECTION=database` for local testing unless you want RabbitMQ.
- Set Sanctum (no extra config needed for basic token flow).
- For Paystack cashback (optional):
	- `PAYSTACK_SECRET` — your Paystack secret key
	- `PAYSTACK_RECIPIENT` — recipient code or id (for demo the code reads this env var)
	- If you do not want real transfers while testing, set `PAYSTACK_DISABLED=true` in a test `.env` or `.env.testing` and the gateway can be guarded in code.

3. Run migrations & seed

```bash
php artisan migrate
php artisan db:seed
```

This creates the schema and seeds sample achievements, badges and a test user.

4. Run the app

```bash
php artisan serve
```

5. Start a queue worker (database queue)

```bash
php artisan queue:work --tries=3
```

If you prefer RabbitMQ, use `docker-compose.yml` included in the repo to run a `rabbitmq` service and set `QUEUE_CONNECTION=rabbitmq`.

## API Endpoints (summary)
- POST /api/register → register and receive a Sanctum token
- POST /api/login → issue token
- POST /api/purchases → enqueue a purchase (authenticated)
- GET /api/users/{user}/achievements → list a user's achievements (authenticated)
- GET /api/admin/users/achievements → admin listing (paginated, filterable)

Notes
- All `/api/*` routes are under `routes/api.php` and protected with `auth:sanctum` where appropriate.
- For JSON responses, send the header `Accept: application/json`.

## Payment / Cashback
- The `PaymentGateway` interface lives at `app/Services/PaymentGateway.php`.
- `PaystackGateway` is implemented at `app/Services/PaystackGateway.php` and performs transfers using Guzzle. It reads `PAYSTACK_SECRET` and `PAYSTACK_RECIPIENT` from env.
- In tests we bind a fake `PaymentGateway` implementation to avoid real network calls.


## Testing
Tests are written using Pest.

Run all tests:

```bash
php artisan test
# or
./vendor/bin/pest
```

Notes for safe testing
- External services (Paystack) are mocked in tests by binding the `PaymentGateway` interface to a fake implementation. Do not run tests against production credentials.

## Troubleshooting
- 419 on API routes: ensure you're calling `/api/*` endpoints (they're loaded by `routes/api.php` which is stateless) and set header `Accept: application/json`. Do not send CSRF cookies for API token auth.
- `personal_access_tokens` table missing: publish and run Sanctum migrations:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

- Tests failing due to facades not bound: ensure tests are bound to `Tests\TestCase` (Pest config or per-test `uses(Tests\TestCase::class)`), and tests run with `APP_ENV=testing`.

## Laravel Reverb Implementation

This project uses [Laravel Reverb](https://laravel.com/docs/12.x/broadcasting#reverb-driver) for real-time event broadcasting.

### Setup
- Reverb is configured as the default broadcaster in `.env`:
  - `BROADCAST_CONNECTION=reverb`
  - `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME` must be set to match your Reverb server instance.
- See `.env.example` for required variables.
- The backend broadcasts events (e.g. `BadgeUnlocked`, `AchievementUnlocked`) on public or private channels.

### Broadcasting Configuration
- See `config/broadcasting.php` for the `reverb` connection setup.
- Channels are defined in `routes/channels.php` for private channel authorization.
- Example event class:
  ```php
  use Illuminate\Broadcasting\Channel;
  // ...
  public function broadcastOn()
  {
      return new Channel('users.'.$this->userId.'.badges');
  }
  ```
- For private channels, use `PrivateChannel` and authorize in `routes/channels.php`.

### Frontend Usage
- The frontend uses Laravel Echo with the `reverb` broadcaster:
  ```js
  import Echo from 'laravel-echo';
  window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    // For private channels:
    // authEndpoint: import.meta.env.VITE_APP_URL + '/broadcasting/auth',
    // auth: { headers: { Authorization: `Bearer ${token}` } },
  });

  // Subscribe to a public channel:
  window.Echo.channel(`users.${userId}.badges`)
    .listen('App\\Events\\BadgeUnlocked', (data) => {
      // handle badge unlocked
    });
    