# Deployment & Site-Provisioning Scripts

Seven scripts. All of them are generic — they take a theme folder name (and business slug, where relevant) as an argument, so the same scripts work for every future business built from this repo, not just Las Vegas Junk Car Buyers.

## The scripts, in the order you'd actually use them

1. **`new-site.sh`** — start a brand new business. Clones `kadence-child-lvjcb`, renames its code prefix throughout, and resets the config to a blank template with `TODO` placeholders.
2. **`install-wordpress.sh`** — for a server with no WordPress on it yet. Downloads WordPress core, creates `wp-config.php`, runs the install wizard, sets permalinks — all via WP-CLI. *(Skip this if WordPress already exists at your target — use `setup.sh` instead.)*
3. **`setup.sh`** — for a server that already has WordPress. One-time prep: checks SSH access, confirms WordPress is really there, installs WP-CLI if it's missing.
4. **`deploy.sh`** — run every time you want to push code live. Backs up the current theme on the server, syncs the new files, then runs `wp <prefix> provision` to create/update every WordPress page from `business-config.php`.
5. **`import-images.sh`** — upload a local folder of correctly-named image files into the media library, so `business-config.php`'s `image_slug` references resolve automatically. No attachment IDs to copy by hand.
6. **`rollback.sh`** — restores the most recent backup `deploy.sh` made, if a deploy needs to be undone.
7. **`update-site-url.sh`** — after DNS/SSL are confirmed working on the real domain, points the WordPress install at it (the one part of "going live" that is actually scriptable — see the DNS/SSL section below for the rest).

## Starting a brand new business, end to end

```bash
./scripts/new-site.sh phoenix-junk-cars
# fill in wordpress/themes/kadence-child-phoenix-junk-cars/inc/business-config.php

export DEPLOY_HOST=... DEPLOY_USER=... DEPLOY_WP_ROOT=... DEPLOY_PATH=...
export DB_NAME=... DB_USER=... DB_PASSWORD=... SITE_TITLE=... SITE_URL=... ADMIN_USER=... ADMIN_PASSWORD=... ADMIN_EMAIL=...

./scripts/install-wordpress.sh kadence-child-phoenix-junk-cars   # brand new server, no WP yet
# — or, if WordPress already exists there —
./scripts/setup.sh kadence-child-phoenix-junk-cars
./scripts/deploy.sh kadence-child-phoenix-junk-cars

./scripts/import-images.sh kadence-child-phoenix-junk-cars ./phoenix-photos
```

## Environment variables

Put these in a local file you don't commit (e.g. `.env.local`, already covered by `.gitignore`'s `.env*` rule) and `source` it before running the scripts.

**Required for `setup.sh`, `deploy.sh`, `rollback.sh`, `import-images.sh`, `update-site-url.sh`:**

```bash
export DEPLOY_HOST=your-server-hostname-or-ip
export DEPLOY_USER=your-ssh-username
export DEPLOY_PATH=/absolute/path/to/wp-content/themes/kadence-child-lvjcb
export DEPLOY_WP_ROOT=/absolute/path/to/wordpress
export DEPLOY_PORT=22   # only needed if your host uses a non-standard SSH port
```

**Additionally required for `install-wordpress.sh`:**

```bash
export DB_NAME=...
export DB_USER=...
export DB_PASSWORD=...
export DB_HOST=localhost   # optional, this is the default
export SITE_TITLE="Phoenix Junk Car Buyers"
export SITE_URL=https://phoenixjunkcars.com
export ADMIN_USER=...
export ADMIN_PASSWORD=...
export ADMIN_EMAIL=...
```

## Automatic deploy on every `git push`

Add these GitHub repo secrets (Settings → Secrets and variables → Actions → New repository secret):

| Secret name | Value |
|---|---|
| `LVJCB_DEPLOY_SSH_KEY` | The **private** SSH key that can log into your server (generate a dedicated deploy key, don't reuse your personal one) |
| `LVJCB_DEPLOY_HOST` | Same as `DEPLOY_HOST` above |
| `LVJCB_DEPLOY_USER` | Same as `DEPLOY_USER` above |
| `LVJCB_DEPLOY_PATH` | Same as `DEPLOY_PATH` above |
| `LVJCB_DEPLOY_WP_ROOT` | Same as `DEPLOY_WP_ROOT` above |
| `LVJCB_DEPLOY_PORT` | Same as `DEPLOY_PORT` above (optional) |

The matching public key needs to be added to `~/.ssh/authorized_keys` for `DEPLOY_USER` on your server — none of these scripts do that step for you, since it requires the key to exist first.

Once those secrets exist, every push to `main` that touches `wordpress/themes/kadence-child-lvjcb/` runs `.github/workflows/deploy-lvjcb.yml` automatically. For a second business, copy that workflow file, change its paths filter and secret names — don't try to make one workflow branch across multiple businesses' credentials.

## Domain, DNS, and SSL — what's scriptable and what isn't

This is the one part of "going live" that genuinely can't be automated generically, because every domain registrar and every host's SSL setup is different, and none of them were given to these scripts.

**What you do manually, in your registrar's and host's own dashboards:**
1. Point the domain's nameservers (or just its A record) at your server's IP address.
2. Wait for DNS to propagate (usually minutes, sometimes up to 24-48 hours).
3. Issue an SSL certificate for the domain — most hosts offer free automatic SSL (Let's Encrypt) through their control panel; if yours doesn't, `certbot` run on the server does the same thing.
4. Confirm `https://your-domain.com` actually loads before moving on.

**What's scriptable, once the above is confirmed working:**

```bash
./scripts/update-site-url.sh http://your-server-ip https://your-real-domain.com
```

This updates WordPress's own `siteurl`/`home` options and rewrites any stored references from the old address to the new one. Running it before DNS/SSL are actually ready will make the site briefly unreachable at both addresses, so confirm step 4 above first.

## What this does not do

- **Generate images.** `import-images.sh` uploads files you already have — see the AI Image Production Guide for the photography direction; producing the actual files (real photography or AI-generated, per that guide) still requires you or a separate tool with image-generation access.
- **Roll back page content.** `rollback.sh` restores theme code only; anything `wp <prefix> provision` changed in the database stays changed.
- **Create the database itself** for `install-wordpress.sh` — most hosts require that through a control panel, not SSH; the script assumes `DB_NAME`/`DB_USER` already exist with the right privileges.
- **Configure DNS or issue SSL certificates** — see above.
