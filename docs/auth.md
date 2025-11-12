# Authentication Guide

SWE Wannabe uses two complementary authentication modes:

1. **Web sessions** for the dashboard / SPA.
2. **Bearer tokens** for the CLI or API integrations.

This document explains how each mode works, how to provision credentials, and how to test them before the Node CLI is ready.

## Web Sessions (Cookie-Based)

- Symfony’s `main` firewall protects every route except `/login`, `/logout`, and profiler assets.
- Users authenticate via the terminal-style form at `/login`, which submits to Symfony’s `form_login`. The `/auth` SPA view documents the process and links to the CLI/device flows.
- Successful login issues an HTTP-only, SameSite Lax session cookie. Logout (`/logout`) invalidates the session and requires a CSRF token embedded in the sidebar form.
- The SPA shell (`templates/base.html.twig`) displays the signed-in user’s name/email and provides a logout button.
- Guard every new route with `#[IsGranted('ROLE_USER')]` or higher roles as needed.

### Creating Users

Use the console to provision local accounts:

```bash
docker compose exec php php bin/console app:user:create learner@example.com "Learner Name" s3cret
docker compose exec php php bin/console app:user:create admin@example.com "Admin Name" strongpass --admin
```

Passwords are hashed automatically. `--admin` appends `ROLE_ADMIN`.

### Testing Sessions

1. Run `docker compose up --wait`.
2. Create a user (see above).
3. Visit `https://localhost/login`, accept the self-signed cert, and log in.
4. Confirm the shell loads, sidebar shows your identity, and `/profile` displays user info.

## API Tokens (Bearer)

CLI clients authenticate via stateless tokens stored hashed in the `api_token` table.

### Device-Code Flow (Roadmap for CLI Login)

To align with the PRD’s “device-code auth for CLI”, the upcoming workflow looks like this:

1. **CLI requests a device code**
   - `POST /api/device-code` returns `{ device_code, user_code, verification_uri, expires_in, interval }`.
   - Backend persists a `DeviceLoginRequest` with hashed codes and expiration.
2. **Learner verifies the code in the browser**
   - `/device` renders a form styled like the rest of the dojo.
   - After signing in (or confirming an existing session), the user enters the `user_code`; the backend marks the request as approved and ensures the GitHub App link exists.
3. **CLI polls for approval**
   - `POST /api/device-token` with the `device_code`.
   - Responses mimic OAuth device flow (`authorization_pending`, `slow_down`, `expired_token`). Once approved, returns `{ access_token, token_type: "bearer", expires_in }`.
   - The backend issues an `ApiToken`, just like `app:token:create`, and updates `last_used_at` on every authenticated call.
4. **CLI stores the token**
   - Ink app writes the token + user metadata to a config file (`~/.swew/config.json`).
   - Subsequent commands send `Authorization: Bearer <token>` to all `/api/*` endpoints.

### Current (Manual) Flow for Testing

Until the device-code endpoints land, you can generate tokens manually:

1. Generate a token:
   ```bash
   docker compose exec php php bin/console app:token:create learner@example.com "CLI on laptop" --expires-in=P30D
   ```
   The command prints a 64-byte hex string once—store it securely. The hash (SHA-256) is persisted alongside metadata (label, optional expiry).

2. Call protected endpoints with `Authorization: Bearer <token>` headers. The `api` firewall uses `App\Security\ApiTokenAuthenticator` to validate tokens and attach the owning user.

3. Tokens can be revoked by deleting the DB row or via a future admin UI.

### Testing Tokens without the Node CLI

After creating a token, call the example endpoint:

```bash
TOKEN=…raw token from step 1…
curl -k \
  -H "Authorization: Bearer $TOKEN" \
  https://localhost/api/profile
```

Expected response:

```json
{
  "email": "learner@example.com",
  "name": "Learner Name",
  "roles": ["ROLE_USER"]
}
```

The `-k` flag ignores the self-signed cert. Remove it once you trust the certificate locally.

### Token Hygiene

- Tokens are hashed with SHA-256; no plain text is stored.
- Optional expiry (`--expires-in`) accepts ISO 8601 intervals (e.g., `P7D`, `P1M`).
- `App\Security\ApiTokenAuthenticator` updates `last_used_at` whenever a token is presented. Expired tokens are rejected automatically.

## Local QA Checklist

1. `docker compose exec php php bin/console doctrine:migrations:migrate`
2. Create a user with `app:user:create`.
3. Visit `/login`, authenticate, ensure shell + `/profile`.
4. Generate a token with `app:token:create`.
5. `curl` `https://localhost/api/profile` with the bearer token to confirm CLI path works.
6. From `swew/`, run `npm install`, `npm run build`, then `npm link` (or `npm install -g file:./swew`) so that the `swew` binary is available globally. Run `SWEW_ACCEPT_SELF_SIGNED=1 swew login` and `SWEW_ACCEPT_SELF_SIGNED=1 swew status` to exercise the device-code flow and verify that the stored bearer token works end-to-end.

Completing these steps validates the end-to-end auth stack before integrating the Ink-based CLI.
