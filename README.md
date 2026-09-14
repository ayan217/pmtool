# Personal PM

A lightweight, single-user Laravel project and task manager for personal development work. Projects are optional. Tasks are the primary object.

## Stack

- Laravel 12
- PHP 8.3+
- MySQL 8+
- Blade + Bootstrap 5 + Bootstrap Icons
- Laravel Mail, Scheduler, and database queues

## Local setup

1. Copy the environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

2. Create a MySQL database named `pmtool` (or change `DB_DATABASE`).

3. Update `.env` with your MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pmtool
DB_USERNAME=root
DB_PASSWORD=
```

Set `APP_TIMEZONE` to your local timezone so deadlines and reminders match the clock you work against.

4. Run migrations and seed the initial user:

```bash
php artisan migrate
php artisan db:seed
```

Default login (change these in `.env` before seeding):

```env
SEED_USER_NAME=Ayan
SEED_USER_EMAIL=ayan@example.com
SEED_USER_PASSWORD=password
```

After the first seed, change the password from **Settings** or by reseeding with a new `SEED_USER_PASSWORD`.

5. Start the app:

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` and sign in.

## SMTP

Configure Laravel Mail in `.env`. Do not hardcode credentials in the repository.

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="you@gmail.com"
MAIL_FROM_NAME="Personal PM"
```

Use a Gmail App Password, not the account login password. `MAIL_USERNAME` and `MAIL_FROM_ADDRESS` should be the same Gmail or Google Workspace address. For local development you can use `MAIL_MAILER=log` to write emails to `storage/logs`.

## Queues

The app uses the database queue driver so reminder emails do not block HTTP requests.

```bash
php artisan queue:work
```

Keep this process running in development. In production, run it under a process manager such as Supervisor:

```ini
[program:pmtool-queue]
process_name=%(program_name)s
command=php /path-to-project/artisan queue:work --sleep=1 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/queue.log
```

## Scheduler

Reminder checks run every 15 minutes through Laravel's scheduler.

```bash
php artisan deadlines:send-reminders
```

Production cron:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Laravel decides when the reminder command should execute.

## Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database.

## Production notes

- Set `APP_ENV=production` and `APP_DEBUG=false`
- Use a real `APP_KEY`
- Point `APP_URL` at the public URL so reminder emails contain working task links
- Serve `public/` from Nginx or Apache
- Run `php artisan migrate --force` on deploy
- Run `php artisan config:cache` and `php artisan route:cache`
- Keep `queue:work` and the scheduler running. On Coolify, `nixpacks.toml` starts both through Supervisor on every redeploy.
