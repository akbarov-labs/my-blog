# Deploying to Contabo

Every push to `main` builds one Docker image on GitHub and pushes it to
GitHub Container Registry. It then goes to **staging**
(`https://my-blog.staging.sarvarlogs.uz`, password protected) automatically,
and to **production** (`https://sarvarlogs.uz`) only when you press
**Actions → Promote to production → Run workflow**. (The repository is
private, and GitHub's free plan has no "Required reviewers" for private
repositories, so that button is the approval step.) Production always runs an image
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
| Backups | `my-blog-backup` → `my-blog_db_backups`, then off-site | none |
| GitHub environment | `production` (via Promote to production) | `staging` |

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
**Actions → Promote to production → Run workflow** → production gets the
image of the latest commit on `main`, the one staging runs.

Wait until the "Build and deploy" run is green before promoting. Don't like
what staging shows? Don't promote: push a fix, and promote once it's on
staging.

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

**Promote to production → Run workflow** with an older full commit SHA in
the box, or on the server:

```bash
cd /opt/apps/my-blog
sudo docker images ghcr.io/akbarov-labs/sarvarlogs-app
sudo IMAGE_TAG=<sha> docker compose -f docker-compose.prod.yml up -d --force-recreate
```

## Backups and restore

The whole blog (posts, projects, profile, messages) lives in one SQLite
file, `storage/app/database.sqlite` in the `my-blog_app_storage` volume. The
`backup` service (`my-blog-backup`) copies it every night at **03:45 UTC**,
and once more every time the container starts:

- with SQLite's `VACUUM INTO`, which is consistent even while the blog
  writes (a plain `cp` of a live database is not);
- checked with `PRAGMA integrity_check`, then gzipped to
  `my-blog-<UTC time>.sqlite.gz` in the `my-blog_db_backups` volume;
- kept `BACKUP_RETENTION_DAYS` days (default 14, set in the server `.env`).

At 04:30 UTC the server's `offsite-backup` job copies new files, encrypted,
to Backblaze B2 (`dumps/my-blog/`, kept 30 days). That one is set up once
for the whole server: `docs/offsite-backups.md` in the Reads repo.

**Turning it on (once).** The service is new in `docker-compose.prod.yml`,
which CI doesn't ship. Mac:

```bash
cd ~/dev/sites/my-blog
git switch main && git pull
scp docker-compose.prod.yml sarvar@169.58.243.86:/tmp/
```

Server:

```bash
sudo install -m 644 -o root -g root /tmp/docker-compose.prod.yml /opt/apps/my-blog/
cd /opt/apps/my-blog
sudo docker compose -f docker-compose.prod.yml up -d backup
sudo docker logs --tail 5 my-blog-backup
```

The log ends in `[backup] … ok: /backups/my-blog-….sqlite.gz (…, N posts)`
and `retained: 1 backup(s)`.

**Check it later:**

```bash
sudo docker logs --tail 20 my-blog-backup
sudo docker exec my-blog-backup ls -la /backups
sudo offsite-backup list dumps/my-blog
```

**Restore.**

1. Get the copy. From the server's own volume, pick a name from the
   `ls -la` above. If the server was lost, fetch it from off-site into the
   volume first:

   ```bash
   sudo offsite-backup list dumps/my-blog
   sudo offsite-backup fetch dumps/my-blog/my-blog-<UTC time>.sqlite.gz "$(sudo docker volume inspect -f '{{.Mountpoint}}' my-blog_db_backups)"
   ```

2. Check it opens. The last two lines should be `ok` and the post count:

   ```bash
   F=my-blog-<UTC time>.sqlite.gz
   sudo docker exec my-blog-backup sh -c "gunzip -c /backups/$F > /tmp/check.sqlite && sqlite3 /tmp/check.sqlite 'PRAGMA integrity_check; SELECT count(*) FROM posts;'"
   ```

3. Stop the blog, swap the file, start it again. Nothing is deleted: the
   current database and any `-journal`/`-wal`/`-shm` file next to it move
   into a `before-restore-<time>` folder. (A leftover journal would be
   replayed onto the restored file.)

   ```bash
   cd /opt/apps/my-blog
   sudo docker compose -f docker-compose.prod.yml stop app
   sudo docker run --rm --volumes-from my-blog-app --volumes-from my-blog-backup alpine:3.20 sh -c "set -e; cd /var/www/html/storage/app; aside=before-restore-\$(date -u +%Y%m%dT%H%M%SZ); mkdir \$aside; for f in database.sqlite database.sqlite-journal database.sqlite-wal database.sqlite-shm; do [ ! -e \$f ] || mv \$f \$aside/; done; gunzip -c /backups/$F > database.sqlite; ls -la . \$aside"
   sudo docker compose -f docker-compose.prod.yml start app
   ```

   `entrypoint.sh` gives `storage/` back to `www-data` and runs migrations
   newer than the copy. Everything written after the copy's time is gone.
   Once the blog looks right, delete the `before-restore-*` folder.

## Notes

- Migrations run automatically on container start (`entrypoint.sh`);
  Laravel only applies new ones. Staging runs them first, on its copy.
- `storage/` is the persistent volume — the SQLite database, logs and
  cached views survive every deploy even though the image is replaced
  wholesale each time. `db_backups` holds the nightly copies (see
  "Backups and restore").
