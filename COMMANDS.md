# Commands

Run every command from the project root:

```text
C:\Users\parad\Desktop\code\personal\pmtool
```

---

## First-time setup

Create the MySQL database first (WAMP MySQL, root, no password):

```powershell
C:\wamp64\bin\mysql\mysql8.4.7\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS pmtool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Then:

```powershell
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Default login after seeding:

```text
email:    ayan@example.com
password: password
```

Change `SEED_USER_NAME`, `SEED_USER_EMAIL`, and `SEED_USER_PASSWORD` in `.env` before seeding if you want different credentials.

---

## Daily local run

Start the app:

```powershell
php artisan serve
```

Then open http://127.0.0.1:8000

In a second terminal, process queued reminder emails:

```powershell
php artisan queue:work
```

---

## Daily database backup

Creates one file and overwrites it on every run:

```text
storage/app/backups/daily.sql
```

Run now:

```powershell
php artisan db:backup
```

This is scheduled every day at 2:00 AM. It only runs if the scheduler cron is active:

```text
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

If `mysqldump` is not on PATH, set the full path in `.env`:

```env
DB_DUMP_BINARY=C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe
```

---

## Deadline reminders

Send reminders now:

```powershell
php artisan deadlines:send-reminders
```

Run whatever the scheduler has due right now:

```powershell
php artisan schedule:list
php artisan schedule:run
```

The reminder command is scheduled every 15 minutes. In production, cron should run:

```text
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## After code or database changes

```powershell
php artisan migrate
php artisan db:seed
php artisan test
```

---

## Useful checks

```powershell
php artisan route:list
php artisan schedule:list
php artisan config:clear
php artisan cache:clear
```

---

## Production deploy

```powershell
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan queue:work --sleep=1 --tries=3
```

---

## Coolify

1. Create a MySQL database resource first.
2. Create an application from this GitHub repo.
3. Set **Build Pack** to `nixpacks`.
4. Set **Ports Exposes** to `80`.
5. Add environment variables (use the Coolify MySQL host, not `127.0.0.1`):

```env
APP_NAME="Personal PM"
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_URL=https://your-domain.example
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=pmtool
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Personal PM"

SEED_USER_NAME=Ayan
SEED_USER_EMAIL=ayan@example.com
SEED_USER_PASSWORD=change-me
```

Generate `APP_KEY` locally with `php artisan key:generate --show` and paste it into Coolify. Do not leave it empty.

On each deploy, Nixpacks/`start.sh` runs:

```bash
php artisan migrate --force
php artisan storage:link
php artisan db:seed --force   # only if no user exists yet
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:work --sleep=1 --tries=3
php artisan schedule:work
```

`schedule:work` keeps deadline reminders and the daily `db:backup` running.

Optional: persist `/app/storage/app/backups` as a Coolify volume so the daily SQL dump survives redeploys.
