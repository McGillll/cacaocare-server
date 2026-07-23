# Latest Updates

## 2026-07-24 — Auth + User modules refactored to layered architecture

**What changed:**
- Auth and User controllers moved to `App\Http\Controllers\Api\{Auth,User}` with Interface → Repository → Service layers (`app/Interfaces`, `app/Repositories`, `app/Services`). `AuthController` no longer statically calls `UserController::store`.
- Response envelope is now `{success, message, data}` on every Auth/User endpoint. Login additionally returns a top-level `token`. 422 validation failures additionally return a top-level `errors` object (unchanged shape from before).
- **Breaking:** `User` routes (`/user/{param}`, `/user/update/verified/{param}`) now resolve by `uuid` instead of integer `id`.
- Admin-only gating added via a new `role:` middleware (`role:admin`) on `GET /user`, `/user/count/all`, `/user/count/today`, `PUT /user/update/verified/{uuid}`. Ownership check (self-or-admin) added in `UserService` for `update`/`destroy`.
- Removed a dead duplicate route: `GET /user` was previously registered twice (`apiResources` + a manual `getMany` route); the manual one was unreachable dead code and has been dropped.
- `POST /auth/login` is now rate-limited (5/min per email+IP).

**Docs updated:** `api/rsc-doc/auth-api.md`, `api/rsc-doc/user-api.md` (both new).

**What the frontend needs to do:** see `client/rsc-doc/frontend-flag.md` (FLAG 1) — switch `UserService.ts`'s `updateUser`/`updateUserStatus` and their call sites in `settings.vue`, `admin/settings.vue`, `UserManagement.vue` from `id` to `uuid`. Not yet done (backend was refactored first; frontend change is tracked separately).
