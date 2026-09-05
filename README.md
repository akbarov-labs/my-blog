# Sarvar Akbarov — Personal Site

Laravel + [Filament](https://filamentphp.com) admin panel, running entirely in Docker. Matches the
imported design (warm minimal blog/portfolio, light & dark themes) with every piece of content —
posts, projects, skills, experience, social links, contact messages, and the homepage bio — editable
from the admin panel instead of hard-coded.

## Requirements

Just **Docker Desktop**. No local PHP, Composer, or MySQL needed — everything (including the
one-time Laravel + Filament install) runs inside containers.

## First-time setup

```bash
cd path/to/my-blog
chmod +x setup.sh
./setup.sh
```

This script (safe to re-run):

1. Scaffolds a fresh Laravel app + the Filament admin package (via a throwaway `composer` container
   — nothing installed on your machine).
2. Copies the custom application on top: models, migrations, Filament admin resources, controllers,
   routes, and the Blade views implementing the design.
3. Builds the app's Docker image and brings up the stack (app, nginx, MySQL).
4. Runs migrations and seeds it with your bio, 4 projects, 5 posts and your social links, so the site
   looks right immediately.

When it finishes:

| | |
|---|---|
| Website | http://localhost:8080 |
| Admin panel | http://localhost:8080/admin |
| Admin login | `admin@example.com` / `password` |

**Change that password immediately** — Admin panel → your name (top right) → Profile.

## Everyday use

```bash
docker compose up -d        # start the stack
docker compose down         # stop it
docker compose logs -f app  # tail the app's logs
docker compose exec app bash            # shell into the app container
docker compose exec app php artisan ...  # run any artisan command
```

## What's editable from the admin panel

| Admin section | Powers |
|---|---|
| **Site Settings** | Name, headline, the two homepage bio paragraphs, footer text |
| **Posts** | Title, slug, category, excerpt, publish date/toggle, and the body as an ordered list of blocks (paragraph / heading / code / quote) — mirrors the post page exactly |
| **Projects** | Name, year, description, tech stack tags, link, display order |
| **Skills** | The chip row under your bio, with display order |
| **Experience** | Role/company entries shown on the homepage |
| **Social & Contact Links** | Email/GitHub/Telegram/etc — toggle whether each shows as a homepage button, on the Contact page, or both |
| **Contact Messages** | Everything submitted through the public contact form |

Categories on the Blog page are derived automatically from whatever category text you put on your
posts — add a new one just by typing it on a post, no separate setup needed.

## Project layout

```
app/Models/            Post, Project, SocialLink, Skill, Experience, Profile, ContactMessage
app/Filament/           Admin panel resources + the Site Settings page
app/Http/Controllers/   Public-facing controllers (Home, Post, Project, Contact)
resources/views/        Blade templates for the public site
database/migrations/    Schema for the models above
database/seeders/       Demo content (your bio, posts, projects, links)
docker/                 Dockerfile (PHP-FPM + extensions) + nginx config
docker-compose.yml      app (PHP-FPM) + webserver (nginx) + db (MySQL)
setup.sh                One-time/idempotent setup script described above
```

## Notes

- `setup.sh` is written defensively (checks, idempotent steps, `--ignore-platform-reqs` where
  relevant), but if a command ever fails partway through, copy the error here and it can be fixed
  quickly — most likely causes would be a port already in use (edit `APP_PORT` / `FORWARD_DB_PORT`
  in `.env` and re-run) or a Filament/Laravel version nuance.
- The app image runs PHP 8.4 (`docker/Dockerfile`) — Laravel 13's dependencies require it.
- The Filament version is pinned to `^3.3` deliberately (Filament v4/v5 changed the Forms/Tables API);
  upgrading later is possible but would need the resource classes touched up for the new API.
- Ports used by default: `8080` (site), `33061` (MySQL, forwarded so you can connect a DB client of
  your choice — TablePlus, Sequel Ace, DBeaver, etc.). Change either in `.env` before running
  `setup.sh` if they collide with something else on your machine.
