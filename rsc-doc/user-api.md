# User API

Base path: `/api`. All routes require `Authorization: Bearer <token>` (`auth:sanctum`).

## Endpoints overview

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/current/user` | any authenticated user | Get the logged-in user |
| GET | `/user` | role: admin | List all users (role=`user`) |
| GET | `/user/count/all` | role: admin | Count of users (role=`user`) |
| GET | `/user/count/today` | role: admin | Count of users created today |
| PUT | `/user/update/verified/{uuid}` | role: admin | Toggle a user's `email_verified_at` |
| GET | `/user/trashed/all` | role: admin | List soft-deleted users |
| PUT | `/user/trashed/restore/{uuid}` | role: admin | Restore a soft-deleted user |
| POST | `/user` | any authenticated user | Create a user directly (admin tooling) |
| GET | `/user/{uuid}` | any authenticated user | Get one user |
| PUT | `/user/{uuid}` | owner or admin | Update a user (multipart — see note) |
| DELETE | `/user/{uuid}` | owner or admin | Soft-delete a user (row retained with `deleted_at` set) and revoke all their access tokens |

## Who can do what

- **Admin-only** (`role:admin` middleware): `index`, `getUserCount`, `getUserSignToday`, `updateUserVerification`, `trashed`, `restore`.
- **Owner-or-admin** (checked in `UserService`, not middleware — the ownership check needs the resolved `{uuid}` from the route, which middleware can't see): `update`, `destroy`. A non-owner, non-admin request throws `UnauthorizedActionException` → 403.

## Soft delete

`users` uses soft deletes. `DELETE /user/{uuid}` sets `deleted_at` instead of removing the row, and also revokes all of the target user's Sanctum tokens (`$target->tokens()->delete()`) so an already-issued session can't keep authenticating past the deletion. A soft-deleted user also can't log back in — `AuthService::login()` looks the user up via `User::where('email', ...)`, and Eloquent's default soft-delete scope excludes trashed rows from that query automatically. `GET /user/trashed/all` lists soft-deleted users (admin-only), and `PUT /user/trashed/restore/{uuid}` restores one (admin-only, resolves the trashed model via `withTrashed()` route binding).

## Breaking change: identifier

**User routes now use `uuid`, not integer `id`.** `GET /user/{uuid}`, `PUT /user/{uuid}`, `DELETE /user/{uuid}`, `PUT /user/update/verified/{uuid}` all resolve by `uuid` via Laravel route-model binding. The `id` field is still present in the response payload for backward compatibility with existing list-diffing code, but must not be used to build request URLs anymore.

The frontend has not been updated for this yet — see `client/rsc-doc/frontend-flag.md` (FLAG 1) for the exact call sites that need to switch from `id` to `uuid`.

## Response envelope

```json
{ "success": true, "message": "string|null", "data": {} }
```
422 validation failures also include a top-level `errors` object.

## User object shape

| Field | Type |
|---|---|
| id | number (internal, do not use in routes) |
| uuid | string |
| username | string |
| email | string |
| profile | string (URL) |
| region | string |
| province | string |
| city | string |
| barangay | string |
| role | `"admin"` \| `"user"` |
| email_verified_at | string \| null |

## PUT /user/{uuid}

Multipart (`POST` transport with `_method=PUT` spoofing — required because file uploads don't parse on native PUT/PATCH).

| Field | Type | Rules |
|---|---|---|
| username | string | sometimes |
| password | string | sometimes, min:8, upper/lower/digit/special char |
| region, province, city, barangay | string | sometimes |
| profile | file (image) | sometimes |

**Errors** — 403 if not the owner and not admin; 422 validation failure.

## Error status summary

| Status | Meaning |
|---|---|
| 403 | Not the resource owner and not admin |
| 404 | User not found for the given uuid |
| 422 | Validation failure |
