# Latest Updates

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

**What changed:** `cacaos` table gained a `deleted_at` column. `DELETE /cacao/{id}` no longer removes the row — it sets `deleted_at`, and the entry disappears from all normal reads (`show`, `feed`, `recent`, analytics/heatmap/graph queries) since Eloquent excludes soft-deleted rows by default.

**Why:** requested so deleted cacao entries can be recovered/audited instead of being permanently lost; matches the pattern already used for `users` (soft-deleted 2026-07-24 as well).

**Docs updated:** `cacao-api.md` (DELETE endpoint description, "What changed" section).

**Frontend impact:** none for normal usage — `DELETE /cacao/{id}` still returns the same response and the entry still disappears from all listings/feeds exactly as before. No restore or trashed-listing endpoint exists yet, so there is nothing new to wire up on the client side.
