# Latest Updates

## 2026-07-24 — Comment module refactored to layered architecture

**What changed:**
- `CommentController` moved to `App\Http\Controllers\Api\Comment` with Interface → Repository → Service layers.
- **Breaking:** all four endpoints now return the uniform `{success, message, data}` envelope. Previously `index` returned the raw paginator with no wrapper, `store` returned the bare comment object, `update` was already `{data: ...}`, and `destroy` returned an empty 204. See `client/rsc-doc/frontend-flag.md` (FLAG 2) for exact frontend changes needed — not yet done.
- **Security fix:** a `CommentPolicy` already existed with an owner-or-admin rule for `update`/`delete` but was never invoked anywhere — any authenticated user could edit or delete *any* comment via a direct API call (the UI only hid the buttons, it didn't stop the request). This is now enforced in `CommentService` (403 `UnauthorizedActionException` for non-owner, non-admin).
- Identifier stays integer `id` — this module was not part of the uuid migration.

**Docs updated:** `api/rsc-doc/comment-api.md` (new).

**Frontend:** not yet updated — see `client/rsc-doc/frontend-flag.md` (FLAG 2). Two call sites in `CommentList.vue` need a `.data`/`.data.data` unwrap; `CommentItem.vue`'s update/delete call sites are unaffected.

## 2026-07-24 — Treatment module refactored to layered architecture

**What changed:**
- `TreatmentController` moved to `App\Http\Controllers\Api\Treatment` with Interface → Repository → Service layers. Nested keypoint create/replace logic lives in `TreatmentService`/`TreatmentRepository` (wrapped in a DB transaction); JSON-string-vs-array normalization for `keypoints` (needed because FormData sends it as a JSON string) lives in the service layer.
- Response envelope is now `{success, message, data}`, matching Auth/User/DownloadLinks. `GET /treatment` and `GET /treatment/{id}` responses are unchanged in shape (`data` = treatment object/array with a nested `keypoints` array), since the envelope preserves the existing `data` key.
- **Behavior change:** `store`/`update`/`destroy` are now `role:admin` only (previously any authenticated user could hit them, though only the admin panel actually did).
- **Removed dead code:** `TreatmentKeyPointController` and its two Form Requests were deleted — they had zero registered routes (keypoints were always managed as a nested part of Treatment, never as a standalone resource). Also removed two broken duplicate policy files (`TratmentPolicy.php`, `TratmentKeyPointPolicy.php`) that referenced a non-existent `Tratment` model.
- `disease` uniqueness is now enforced by request validation (422 with a clean message) instead of surfacing as an unhandled SQL constraint error.
- `GET /treatment` (index) stays public/no-auth, matching original behavior (used by the user-facing treatment-guide page).

**Docs updated:** `api/rsc-doc/treatment-api.md` (new).

**Frontend:** no code changes required — the only caller of `store`/`update`/`destroy` is the admin panel (`TreatmentLibrary.vue`), which already runs as admin. `update`'s existing FormData + `_method=PUT` + JSON-string `keypoints` pattern is unchanged and still supported.

## 2026-07-24 — DownloadLinks module refactored to layered architecture

**What changed:**
- `DownloadLinksController` moved to `App\Http\Controllers\Api\DownloadLink` with Interface → Repository → Service layers.
- Response envelope is now `{success, message, data}` on every DownloadLinks endpoint, matching Auth/User.
- **Behavior change:** `store`/`update`/`destroy` are now `role:admin` only (previously any authenticated user could hit them, though only the admin panel actually did). `index`/`show`/`latestDate` remain open to any authenticated user; `latest` stays public.
- `version` uniqueness is now enforced by request validation (422 with a clean message) instead of surfacing as an unhandled SQL constraint error.
- Identifier stays integer `id` — this module was not part of the uuid migration.

**Docs updated:** `api/rsc-doc/download-link-api.md` (new).

**Frontend:** no code changes required — the only caller of `store`/`update`/`destroy` is the admin panel (`AppUpdates.vue`), which already runs as an admin. Response shape (`response.data`) is unchanged.

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
