# Deploying to Contabo

Every push to `main` builds a Docker image on GitHub, pushes it to GitHub
Container Registry, then SSHs into your Contabo server to pull it and
restart — the same box already running kitoblarim behind Caddy.

**Footprint:** one container (nginx + PHP-FPM together, ~150–250MB RAM),
SQLite instead of a separate MySQL server, joins the Caddy network you
already have — no new reverse proxy, no second database process.

## One-time server setup

Do this once, by hand, over SSH.

**1. Find Caddy's Docker network**

```bash
docker inspect <your-caddy-container-name> --format '{{json .NetworkSettings.Networks}}'
```

Note the network name. If `docker-compose.prod.yml`'s `caddy_net` doesn't
match it, edit that one line in the repo before the first deploy.

**2. Create the deploy directory and the real `.env`**

```bash
mkdir -p /opt/apps/my-blog
cd /opt/apps/my-blog
```

Generate an app key:

```bash
docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Create `.env` here (copy `.env.production.example` from the repo as a
starting point) and paste that key into `APP_KEY=`. This file stays on the
server permanently — deploys never touch it.

**3. Add the Caddy site block**

Copy the block from `deploy/Caddyfile.snippet` in the repo into your
existing Caddyfile, then reload Caddy (see the comment in that file for the
exact command). Caddy will fetch the Let's Encrypt certificate for
`sarvarlogs.uz` automatically the first time it's requested.

**4. Add the GitHub repo secrets**

Repo → Settings → Secrets and variables → Actions → New repository secret:

| Secret | Value |
|---|---|
| `CONTABO_HOST` | Server IP or hostname |
| `CONTABO_USER` | SSH username |
| `CONTABO_PORT` | SSH port (usually `22`) |
| `CONTABO_SSH_KEY` | Private key (PEM) for a key whose public half is in that user's `~/.ssh/authorized_keys` |
| `CONTABO_DEPLOY_PATH` | `/opt/apps/my-blog` (or wherever you created it in step 2) |

Don't reuse a personal key you need elsewhere — generate a dedicated
deploy keypair (`ssh-keygen -t ed25519 -f deploy_key -N ""`), add
`deploy_key.pub` to the server's `authorized_keys`, and put the contents of
`deploy_key` (the private half) into `CONTABO_SSH_KEY`.

**5. First deploy**

Push to `main` (or run the workflow manually from the Actions tab). Watch
it in the Actions tab. Once it's green:

```bash
docker ps            # my-blog-app should be Up
docker compose -f docker-compose.prod.yml logs -f app
```

Visit `https://sarvarlogs.uz`.

## Everyday use

Just push to `main`. That's the whole pipeline — build, push to GHCR, pull
on the server, restart, run any new migrations automatically.

## Rollback

Every image is also tagged with its commit SHA. To roll back:

```bash
cd /opt/apps/my-blog
IMAGE_TAG=<previous-commit-sha> docker compose -f docker-compose.prod.yml up -d
```

## Notes

- Migrations run automatically on container start (`entrypoint.sh`) — safe
  to leave as-is; Laravel only applies new ones.
- `storage/` is the one persistent volume — your SQLite database, logs, and
  cached views survive every deploy even though the image is replaced
  wholesale each time.
- If you'd rather use MySQL later, that's a small change (swap `DB_CONNECTION`,
  add a `db` service, mount a volume for it) — say the word and it's a quick
  follow-up.
