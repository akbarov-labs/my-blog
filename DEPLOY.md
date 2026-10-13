# Deploying to Contabo

Every push to `main` builds one Docker image on GitHub and pushes it to
GitHub Container Registry. It then goes to **staging**
(`https://my-blog.staging.sarvarlogs.uz`, password protected) automatically,
and to **production** (`https://sarvarlogs.uz`) only after you click
**Approve and deploy** in the Actions tab. Production always runs an image
that already ran on staging — new migrations included, since the container
runs them on start.

**Footprint:** one container (nginx + PHP-FPM together, ~150–250MB RAM),
SQLite instead of a separate database server, routed by the shared `edge`
Caddy through Docker labels. Staging is a second container of the same
size with its own copy of the database.

## How a deploy reaches the server

CI logs in as `deployer`, a user that can only run
`/usr/local/sbin/deploy-app my-blog <sha>` (or `my-blog-staging` with the
staging key) and has no shell. Everything that decides what runs — the
compose file, `.env`, the image name — lives on the server, owned by root,
so a leaked CI key can only redeploy an existing image of this blog.

| | Production | Staging |
|---|---|---|
| Folder | `/opt/apps/my-blog` | `/opt/apps/my-blog-staging` |
| Container | `my-blog-app` | `my-blog-staging-app` |
| Storage volume | `my-blog_app_storage` | `my-blog-staging_app_storage` |
| GitHub environment | `production` (Required reviewers) | `staging` |

The server-wide setup and the tools are in the `akbarov-labs/reads` repo:
`docs/server-hardening.md`, and `docs/add-app.md` for moving a project
(oʻzbekcha: `docs/add-app.uz.md`).

## Moving this blog over (once)

Follow `docs/add-app.md` in the Reads repo with:

```bash
deploy/prepare-app my-blog ~/dev/sites/my-blog
```

on the Mac, and on the server:

```bash
sudo add-app my-blog ghcr.io/akbarov-labs/sarvarlogs-app /home/sarvar/my-blog
```

Use the image name `docker ps` shows for `my-blog-app`, without the `:tag`.

Then, before merging, two steps only this blog needs:

**Staging settings.** Open `sudo nano /opt/apps/my-blog-staging/.env` and
set `APP_ENV=staging`. `MAIL_MAILER=log` is already the default, so
staging sends no email; keep it that way.

**Staging's database: a copy of production's.** Without it, staging starts
with an empty blog. Copy the storage volume once, before the first staging
deploy:

```bash
sudo docker volume create \
  --label com.docker.compose.project=my-blog-staging \
  --label com.docker.compose.volume=app_storage \
  my-blog-staging_app_storage
sudo docker run --rm \
  -v my-blog_app_storage:/from:ro \
  -v my-blog-staging_app_storage:/to \
  alpine cp -a /from/. /to/
```

Copy it again whenever you want fresh data on staging (stop
`my-blog-staging-app` first).

The production volume keeps its name after the move: compose names it after
the folder, and the folder is still called `my-blog`.

## Everyday use

Push to `main` → staging updates → look at it (username `staging`) →
**Approve and deploy** → production gets that same image.

Don't approve a build you don't like: push a fix and approve the newer run.

A change to `docker-compose.prod.yml` or `docker-compose.staging.yml` is
not shipped by CI. Copy it to the server by hand:

```bash
scp docker-compose.prod.yml sarvar@169.58.243.86:/tmp/
ssh -t sarvar@169.58.243.86 'sudo install -m 644 -o root -g root /tmp/docker-compose.prod.yml /opt/apps/my-blog/'
```

## Changing `.env` on the server

`sudo nano /opt/apps/my-blog/.env`, then recreate the container (a plain
`restart` keeps the old env and labels):

```bash
cd /opt/apps/my-blog
sudo docker compose -f docker-compose.prod.yml up -d --force-recreate
```

## Rollback

Re-run an older successful run from the Actions tab, or on the server:

```bash
cd /opt/apps/my-blog
sudo docker images ghcr.io/akbarov-labs/sarvarlogs-app
sudo IMAGE_TAG=<sha> docker compose -f docker-compose.prod.yml up -d --force-recreate
```

## Notes

- Migrations run automatically on container start (`entrypoint.sh`);
  Laravel only applies new ones. Staging runs them first, on its copy.
- `storage/` is the one persistent volume — the SQLite database, logs and
  cached views survive every deploy even though the image is replaced
  wholesale each time.
