# Deploying to Contabo

Every push to `main` builds a Docker image on GitHub, pushes it to GitHub
Container Registry, then SSHs into your Contabo server to pull it and
restart — the same box already running kitoblarim behind Caddy.

**Footprint:** one container (nginx + PHP-FPM together, ~150–250MB RAM),
SQLite instead of a separate MySQL server, joins the shared `edge` Caddy network — no new reverse proxy, no second database process.

## One-time server setup

Do this once, by hand, over SSH.

**1. Confirm the `edge` network exists**

Public routing goes through the standalone `edge` Caddy
(caddy-docker-proxy, `~/edge` on the server) — the same setup Reads and
Reads-admin use. It reads routing straight off this container's Docker
labels (`caddy: ${DOMAIN}` / `caddy.reverse_proxy: {{upstreams 80}}` in
`docker-compose.prod.yml`). The old setup (joining kitoblarim's
`kitoblarim_default` network + a hand-edited Caddyfile) is gone.

```bash
docker network inspect edge >/dev/null && echo "edge exists"
```

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

**3. Set `DOMAIN` in `.env`**

There is no Caddyfile to edit. Just make sure the server's `.env` contains
`DOMAIN=sarvarlogs.uz` (compose refuses to start without it). Caddy fetches
the Let's Encrypt certificate automatically the first time it's requested.

After editing `.env`, recreate the container (a plain `restart` keeps the old
env and labels):

```bash
docker compose -f docker-compose.prod.yml up -d --force-recreate
```

**4. Create the `production` environment and add its secrets**

The `deploy` job in `deploy.yml` targets a GitHub **environment** named
`production` (this also gives you a deployment record + a clickable link to
`https://sarvarlogs.uz` in the Actions run, and lets you require a manual
approval before every deploy if you want one).

Repo → Settings → Environments → New environment → name it exactly
`production` (case-sensitive, must match `deploy.yml`) → then add these as
**environment secrets** (not repo-level secrets):

| Secret | Value |
|---|---|
| `CONTABO_HOST` | Server IP or hostname |
| `CONTABO_USER` | SSH username |
| `CONTABO_PORT` | SSH port (usually `22`) |
| `CONTABO_SSH_KEY` | Private key (PEM) for a key whose public half is in that user's `~/.ssh/authorized_keys` |
| `CONTABO_DEPLOY_PATH` | `/opt/apps/my-blog` (or wherever you created it in step 2) |

`GITHUB_TOKEN` (used to push to GHCR in the `build` job) needs nothing from
you — GitHub generates and injects it automatically on every run.

Don't reuse a personal key you need elsewhere — generate a dedicated
deploy keypair (`ssh-keygen -t ed25519 -f ~/.ssh/deploy_key_my_blog -N ""`),
add the `.pub` half to the server's `authorized_keys`, and put the contents
of the private half into `CONTABO_SSH_KEY`.

Optional but recommended since this server also runs kitoblarim: on the
`production` environment's settings page, turn on "Required reviewers" and
add yourself. That makes every deploy pause for a manual click in the
Actions tab after the image builds, before it ever touches the server.

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
