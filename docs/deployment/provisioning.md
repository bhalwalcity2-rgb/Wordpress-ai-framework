# Provisioning Paths

| Field | Value |
|---|---|
| Status | Investigated 2026-09-03 — **blocked, system left unchanged** |
| Applies to | `.github/workflows/deploy-lvjcb.yml`, `scripts/deploy.sh`, `wordpress/themes/kadence-child-lvjcb/provision.php` |
| Related | `ai/DEPLOYMENT_STANDARDS.md`, `docs/architecture/content-schema.md` |

Provisioning is the step that turns `business-config.php` and the content files into actual WordPress pages, and imports theme images into the media library. Two deploy paths exist and they do not provision the same way.

---

## 1. The two paths

| | `scripts/deploy.sh` | `.github/workflows/deploy-lvjcb.yml` |
|---|---|---|
| Transport | SSH + rsync | FTPS |
| Runs | manually | on push to `main` — **this is the live path** |
| Ships theme code | yes | yes |
| Creates/updates pages | `wp lvjcb provision` over SSH | **no** |
| Imports media | `scripts/import-images.sh` (`wp media import`) | **no** |
| Credentials | `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_PATH`, `DEPLOY_WP_ROOT`, SSH key | `LVJCB_FTP_HOST`, `LVJCB_FTP_USER`, `LVJCB_FTP_PASSWORD` |

`wp lvjcb provision` (`inc/cli.php`) is the canonical provisioner. The FTPS workflow cannot reach it, which is why `provision.php` — a browser-run script — still exists and is retained.

---

## 2. Can the FTPS workflow invoke WP-CLI?

**No, not with what is present today.** WP-CLI needs shell access on the server; FTPS is file transfer only. Each route was evaluated:

| Route | Verdict |
|---|---|
| Run `wp` over SSH from the workflow | **Blocked.** No SSH secret exists — the repository's only configured secrets are `LVJCB_FTP_HOST`, `LVJCB_FTP_USER`, `LVJCB_FTP_PASSWORD`, `PEXELS_API_KEY`. Whether the host even offers SSH is unverified. |
| `curl` the existing `provision.php` from CI | **Blocked.** It gates on `current_user_can( 'manage_options' )`, so it needs an authenticated admin session. A CI request has none. |
| Add a token-authenticated provisioning endpoint | **Blocked for now.** Writing a publicly reachable, secret-authenticated endpoint that mutates pages is a new attack surface and a new architectural decision. It would need an ADR and a security review, not an incidental change. |
| Upload a PHP file that self-executes on next request | **Rejected.** A file that runs privileged work on an unauthenticated hit is the pattern security hardening exists to prevent. |
| WP-CLI over HTTP | **Not a thing.** WP-CLI has no HTTP transport. |

The blocker is credentials and host capability, not workflow design. Nothing was changed.

---

## 3. If SSH turns out to be available

This is the smallest correct fix, and it introduces no new technology — `scripts/deploy.sh` already does the work.

1. Confirm the host offers SSH and that WP-CLI is installed or installable (`deploy.sh` already probes for `wp` and `$HOME/bin/wp`).
2. Add repository secrets: `LVJCB_SSH_HOST`, `LVJCB_SSH_USER`, `LVJCB_SSH_KEY`, `LVJCB_WP_ROOT`, `LVJCB_THEME_PATH`.
3. Append a step to the existing `deploy` job, after the FTPS transfer, that opens SSH with the key and runs `wp --path=<root> lvjcb provision`. Keep FTPS as the transport; SSH is used only for the provisioning call.
4. Verify the run reports the expected page count and the primary-city skip.
5. Only then retire `provision.php`, and remove the media-import step from it *last* — confirm `scripts/import-images.sh` has covered the images first.

Order matters: `provision.php` is the only provisioner on the live path, so it must be replaced before it is removed, not the reverse.

---

## 4. Meanwhile

`provision.php` stays. Its docblock records why, and warns against the earlier instruction to delete it after running. Its page lists are hardcoded rather than read from `business-config.php`, so they can drift from what `inc/cli.php` would produce — the reason to replace it, but not a reason to remove it while it is the only thing doing the job.

One mitigation already in place: `inc/seo.php` supplies titles, descriptions, and schema to Rank Math through filters at render time, so page metadata is correct even when provisioning never wrote the Rank Math meta fields.
