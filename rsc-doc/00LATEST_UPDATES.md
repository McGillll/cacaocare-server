# Latest Updates

## 2026-07-27 — Treatment update/destroy now route by uuid, not id

**What changed:**
- `treatments` gained a nullable-then-required, unique `uuid` column (added via a new additive migration `add_uuid_to_treatments_table`, existing rows backfilled with generated UUIDs, then the column altered to `NOT NULL` and indexed via a unique constraint — matches the pattern used for `photo_hash` on `cacaos`).
- `Treatment` model now uses the `UsesUuid` trait, so every new treatment gets a `uuid` automatically going forward; `uuid` was added to `$fillable`.
- `PUT /treatment/{uuid}` and `DELETE /treatment/{uuid}` now route-model-bind on `uuid` instead of the internal integer `id` (`Route::put('treatment/{treatment:uuid}', ...)` / `Route::delete('treatment/{treatment:uuid}', ...)` in `routes/api/treatment.php`). `GET /treatment` (index) and `GET /treatment/{id}` (show) are unaffected and still resolve by `id`.
- `TreatmentResource` now exposes `uuid` alongside `id` so callers have something to route update/destroy calls with.
- `treatment_key_points` was intentionally left untouched — it has no standalone routes (keypoints only ever get written nested inside a Treatment's create/update payload), so it doesn't need a `uuid` column.

**Why:** a predictable sequential integer in a mutating endpoint's URL is guessable/enumerable; `uuid` isn't. This matches the same fix already applied to `cacao`'s `update`/`destroy`/`purge` routes and other modules.

**Docs updated:** `treatment-api.md` (endpoints table Path column for update/destroy, corrected the old "Identifier stays integer `id` (no uuid)" note to the same scoped phrasing `cacao-api.md` uses, object shape table, PUT section heading).

**Frontend impact:** breaking for the admin panel. `client/composables/api/sevices/TreatmentService.ts`'s `updateTreatment()` builds its URL from `param.get('id')` (`treatment/${param.get('id')}?_method=PUT`), and `client/components/admin/TreatmentLibrary.vue`'s `handleSave()` appends `formData.append('id', String(updatedTreatment.id))` — both need to switch to the treatment's `uuid` once this ships, or `PUT /treatment/{id}` will start 404ing (id-based lookups no longer match the uuid-bound route). No `destroy`/delete call was found wired up anywhere in `client/` today, so nothing to update there yet, but any future delete UI must use `uuid` too. This backend change was not accompanied by a frontend fix — that's a follow-up.

## 2026-07-27 — DownloadLink update/destroy now bind on uuid

**What changed:**
- `download_links` gained a `uuid` column (nullable at first, then backfilled for every existing row, then altered to `NOT NULL UNIQUE`). `DownloadLinks` now uses the `UsesUuid` trait, so every new row gets a uuid automatically on create.
- `PUT /download_link/{id}` and `DELETE /download_link/{id}` became `PUT /download_link/{uuid}` and `DELETE /download_link/{uuid}` — the route parameter now binds on the `uuid` column instead of the internal integer `id`. `GET /download_link` and `GET /download_link/{id}` are unchanged and still bind on integer `id`.

**Why:** brings `DownloadLink` in line with the established `uuid`-as-external-identifier pattern already used by `Cacao`/`User`, so internal integer ids stop leaking through mutation routes.

**Docs updated:** `download-link-api.md` (endpoints table Path column for update/destroy, object shape table, PUT section, new "What changed" section).

**Frontend impact:** `client/composables/api/sevices/DownloadLinkService.ts`'s `updateDownloadLink(id, ...)` and `deleteDownloadLink(id)` are called from `client/components/admin/AppUpdates.vue` using `updatedItem.id` / `downloadLink.id` (see `DownLoadLink` interface in `client/composables/model/DownloadLinks.ts`, which only has `id`, no `uuid`). These calls will now 404 since the routes bind on `uuid`. The frontend needs to: add `uuid` to the `DownLoadLink` interface, have the backend response include it, and switch `AppUpdates.vue` to pass `.uuid` instead of `.id` for update/delete. This was not changed as part of this backend task — flagging for a follow-up frontend change.

## 2026-07-27 — Comments now use uuid for update/delete routes

**What changed:**
- `comments` gained a `uuid` column (nullable at first, backfilled for every existing row via a chunked loop, then altered to `NOT NULL UNIQUE`). `Comment` now uses the `UsesUuid` trait so every new comment gets a uuid automatically.
- `PUT /comments/{id}` → `PUT /comments/{uuid}` and `DELETE /comments/{id}` → `DELETE /comments/{uuid}` — both now bind on the `uuid` column via route-model binding (`{comment:uuid}`), not the internal integer `id`.
- `GET /cacaos/{cacao}/comments`, `POST /cacaos/{cacao}/comments`, and the `cacao` route param are unaffected. The comment object still returns `id` (now alongside `uuid`).

**Why:** matches the route-enumeration hardening already applied to `Cacao`/`User` — external identifiers should be non-sequential uuids, not guessable integer ids.

**Docs updated:** `comment-api.md` (endpoints table, object shape table, PUT/DELETE section headers, new "What changed" note).

**Frontend impact:** `client/composables/api/sevices/CommentService.ts` calls `updateComment(commentId, ...)` and `deleteComment(commentId)`, both hitting `/comments/${commentId}`, and `client/components/comment/CommentItem.vue` passes `props.comment.id` as that id. These now need to pass `props.comment.uuid` instead, and `CommentResource`/the comment object already includes `uuid` in its response shape to make that possible. Not changed here — out of scope for this backend task — but this is a real, existing caller and will break until updated.

## 2026-07-27 — Cacao photo dedup, delete no longer destroys the R2 file, new admin purge endpoint

**What changed:**
- `cacaos` gained a nullable, indexed `photo_hash` column (SHA-256 of the raw uploaded photo bytes, pre-WebP-encode).
- `POST /cacao` now checks `photo_hash` (including soft-deleted rows) before doing any encode/upload work, and branches into 5 outcomes: normal create (`201`, unchanged shape), duplicate-by-you-active (`409`, new shape), restore-your-soft-deleted-entry (`200`, new shape/message `"Scan restored."`), duplicate-by-someone-else-active (`409`, new reduced-field shape), duplicate-by-someone-else-but-their-row-is-soft-deleted (treated as no match — new row created, existing file reused).
- `DELETE /cacao/{uuid}` (owner-or-admin) no longer deletes the R2 photo — it only soft-deletes the row now. Message/status/envelope unchanged.
- New `DELETE /cacao/{id}/purge` (admin only): ensures the row is soft-deleted, then deletes the R2 file only if no other non-deleted row shares the same `photo_hash`.

**Why:** the photo bucket doubles as a growing dataset for training future ML models, so images must not be casually destroyed. `destroy` was unconditionally hard-deleting the R2 file right after soft-deleting the row, defeating the entire purpose of the soft-delete added on 2026-07-24. Separately, uploads had no dedup — the same photo bytes re-uploaded by the same or different user always re-encoded and stored a brand-new file.

**Docs updated:** `cacao-api.md` (endpoints table, object shape note, full `POST /cacao` rewrite with all 5 outcomes, `DELETE /cacao/{uuid}` behavior note, new `DELETE /cacao/{id}/purge` section, error status summary).

**Frontend impact:** `POST /cacao` callers must now handle `409` (two new shapes — full cacao vs. reduced cacao) and a `200`-with-`"Scan restored."` outcome in addition to the existing `201` success path. `DELETE /cacao/{uuid}` callers see no shape change, but should no longer assume the underlying photo is gone after calling it — only `DELETE /cacao/{id}/purge` (admin-only, new) removes the file.

## 2026-07-24 — Deleting a user now revokes their access tokens

**What changed:** `DELETE /user/{uuid}` (`UserService::delete()`) now calls `$target->tokens()->delete()` after the soft delete, so a user's already-issued Sanctum tokens stop working immediately instead of remaining valid until their 30-day natural expiry.

**Why:** since `users` is soft-deleted (not hard-deleted), the row — and any tokens pointing at it — still exist after "deletion"; without this, a deleted user stayed authenticated on any device where they were already logged in. Login itself was already safe (Eloquent's soft-delete scope excludes trashed rows from the `User::where('email', ...)` lookup in `AuthService::login()`), but existing sessions were not.

**Docs updated:** `user-api.md` (DELETE endpoint description, new "Soft delete" section).

**Frontend impact:** none for normal usage — a deleted user's app will simply start getting 401s on its next request instead of staying logged in, which is the intended fix.

## 2026-07-24 — Cacao entries can no longer have their photo changed on update

**What changed:** `PUT /cacao/{id}` no longer accepts a `photo` field. The photo is set once on `POST /cacao` (create) and is now immutable afterward — `CacaoUpdateRequest` dropped the `photo` validation rule, and the controller/service/repository update path no longer touches the photo at all (no re-upload, no old-photo cleanup).

**Why:** requested so a cacao entry's photo evidence can't be swapped out after the fact.

**Docs updated:** `cacao-api.md` (PUT endpoint field table + note).

**Frontend impact:** none — no client currently calls `PUT /cacao/{id}` (`CacaoService.ts` has no update method), so nothing to change on the frontend.

## 2026-07-24 — Cacao entries are now soft-deleted

**What changed:** `cacaos` table gained a `deleted_at` column. `DELETE /cacao/{uuid}` no longer removes the row — it sets `deleted_at`, and the entry disappears from all normal reads (`show`, `feed`, `recent`, analytics/heatmap/graph queries) since Eloquent excludes soft-deleted rows by default.

**Why:** requested so deleted cacao entries can be recovered/audited instead of being permanently lost; matches the pattern already used for `users` (soft-deleted 2026-07-24 as well).

**Docs updated:** `cacao-api.md` (DELETE endpoint description, "What changed" section).

**Frontend impact:** none for normal usage — `DELETE /cacao/{uuid}` still returns the same response and the entry still disappears from all listings/feeds exactly as before. No restore or trashed-listing endpoint exists yet, so there is nothing new to wire up on the client side.
