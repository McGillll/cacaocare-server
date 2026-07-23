# Auth API

Base path: `/api/auth`

## Endpoints overview

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/auth/login` | none | Log in, returns Sanctum token |
| POST | `/auth/logout` | Bearer token | Revoke current token |
| POST | `/auth/register` | none | Create account, sends verification email |
| POST | `/auth/resend-verification` | none | Re-send verification email |
| GET | `/verify-email?token=` | none (web route) | Verify email, redirects to frontend |
| POST | `/auth/forgot-password` | none | Send password reset email |
| POST | `/auth/reset-password` | none | Reset password using emailed token |

## Response envelope

Every JSON response uses:

```json
{
  "success": true,
  "message": "string|null",
  "data": {}
}
```

Field-level validation failures (422) additionally include a top-level `errors` object: `{ "field": ["message"] }`. Login's success response additionally includes a top-level `token` field.

## POST /auth/login

Rate-limited: 5 attempts/minute per email+IP (`throttle:login`).

**Request body**

| Field | Type | Rules |
|---|---|---|
| email | string | required, email |
| password | string | required |

**Success response** — 200

```json
{
  "success": true,
  "message": null,
  "token": "<sanctum-plain-text-token>",
  "data": { "uuid": "...", "username": "...", "email": "...", "role": "user", "email_verified_at": "..." }
}
```

**Errors**
- 401 — credentials don't match: `{ "success": false, "message": "These credentials do not match our records.", "data": null }`
- 422 — validation failure (missing/invalid email or password)
- 429 — too many attempts (rate limited)

## POST /auth/logout

Requires `Authorization: Bearer <token>`. Revokes the current access token.

**Success response** — 200: `{ "success": true, "message": "Successfully logged out.", "data": null }`

## POST /auth/register

**Request body**

| Field | Type | Rules |
|---|---|---|
| username | string | required, unique |
| email | string | required, email, unique |
| password | string | required, min:8, must contain upper/lower/digit/special char |
| barangay | string | required |
| region | string | sometimes |
| province | string | sometimes |
| city | string | sometimes |

Creates the user (role `user`), sends a verification email.

**Success response** — 200: `{ "success": true, "message": "Verification email sent.", "data": null }`
**Errors** — 422 validation failure.

## POST /auth/resend-verification

| Field | Type | Rules |
|---|---|---|
| email | string | required, email |

**Success response** — 200: `{ "success": true, "message": "Verification email sent!", "data": null }`

## GET /verify-email?token=

Web route (not JSON). Redirects to `https://cacao-care.nuxt.dev/email-verified` on success, or `.../verify-failed` if the token is invalid/expired.

## POST /auth/forgot-password

| Field | Type | Rules |
|---|---|---|
| email | string | required, email |

**Success response** — 200: `{ "success": true, "message": "If this email is registered, a password reset link has been sent.", "data": null }`
**Errors** — 404 if email isn't registered.

## POST /auth/reset-password

| Field | Type | Rules |
|---|---|---|
| email | string | required, email |
| token | string | required |
| password | string | required, min:8, must contain upper/lower/digit/special char |

**Success response** — 200: `{ "success": true, "message": "Password has been successfully reset.", "data": null }`
**Errors**
- 404 — invalid token/email, or user not found
- 422 — token expired (older than 1 hour)

## Error status summary

| Status | Meaning |
|---|---|
| 401 | Bad login credentials |
| 403 | Role/ownership check failed |
| 404 | Email/token/user not found |
| 422 | Validation failure or expired reset token |
| 429 | Login rate limit exceeded |
