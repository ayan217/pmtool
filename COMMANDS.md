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
