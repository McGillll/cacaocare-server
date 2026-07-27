# Cacao API

Base path: `/api`. A Cacao entry is an AI-classified pod scan (label = disease/health status, confidence, photo, uploader). This is the largest module — it also aggregates heatmap/graph/region-alert data used by the admin dashboard.

**Note on other clients:** `store` (and, less certainly, `show`/`update`/`destroy`) may be used by a mobile app not present in this repo. Their request/response shapes were preserved deliberately during this refactor — see call-outs below.

## Endpoints overview

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/cacao` | any authenticated user | Create a cacao entry (mobile upload); dedups by photo hash — see below |
| GET | `/cacao/feed/{order}/{filter}` | any authenticated user | Paginated feed (6/page), `?username=` search |
| GET | `/cacao/{id}` | any authenticated user | Get one entry |
| PUT | `/cacao/{uuid}` | owner or admin | Update an entry |
| DELETE | `/cacao/{uuid}` | owner or admin | Soft-delete an entry (row is retained with `deleted_at` set; the R2 photo is **not** touched) |
| DELETE | `/cacao/{uuid}/purge` | admin only | Hard-purge: ensures the row is soft-deleted, then removes its R2 photo unless another non-deleted row still references the same photo |
| GET | `/cacao/recent/upload` | any authenticated user | Latest 3 uploads — `data` = `{ "cacao": [...] }`, not the array directly |
| GET | `/cacao/status/count` | any authenticated user | Healthy/diseased/all counts |
| GET | `/cacao/upload/today` | any authenticated user | Count uploaded in last 24h |
| GET | `/cacao/upload/years/option` | any authenticated user | Distinct years with uploads |
| GET | `/cacao/graph/yearly` | any authenticated user | Monthly blackpod/frostypod counts for a year, `?year=&region=&city=` |
| GET | `/cacao/upload/trend` | any authenticated user | Last-5-months blackpod/frostypod trend |
| GET | `/cacao/heatmap/{filter}` | any authenticated user | This week's heatmap by city/barangay/region |
| GET | `/cacao/heatmap/{filter}/{date}` | any authenticated user | Heatmap for a given year |
| GET | `/cacao/region/alert/{date}` | any authenticated user | Region alert table for a given year |
| GET | `/cacao/upload/count/{userId}` | any authenticated user | Healthy/diseased/all counts for one user |
| GET | `/cacao/user/upload/{userId}` | any authenticated user | Paginated (9/page) uploads by one user |
| GET | `/cacao/disease/weeks` | any authenticated user | Highest-count diseased label(s) in the last 7 days — `data` = `{ "cacao": ... }`, not the value directly |

`show` and every list/analytics endpoint still route by integer `id`. `update`, `destroy`, and `purge`
route by `uuid` instead — a sequential integer in a mutating endpoint's URL is guessable/enumerable,
so those three now take the non-sequential public identifier. This is scoped per-route
(`Route::put('cacao/{cacao:uuid}', ...)` etc. in `routes/api/cacao.php`), not a model-wide change —
`show`/`feed`/analytics deliberately keep resolving by `id`.

## Who can do what

- **Any authenticated user**: everything except `update`/`destroy`/`purge`.
- **Owner-or-admin** (checked in `CacaoService`): `update`, `destroy` — newly implemented; previously `update` didn't exist as a method at all (calling the registered route would 500) and `destroy` was an empty no-op. Neither had a known caller, but both are now real, gated the same way as Comment/User.
- **Admin only** (`role:admin` route middleware, matching `user`'s admin-only routes): `purge`.

## What changed vs. the original code (and why)

- **Fixed a real bug in `show`**: the old code did `Cacao::where('uploaderId', $id)->first()` — looking up by *uploader* id, not the entry's own id, so it returned some arbitrary cacao belonging to that user rather than the requested one. Route-model binding now resolves the correct entry by its own id.
- **`store` validation is now actually wired.** The old `CacaoStoreRequest` class existed but was never used — the controller was type-hinted `Request $request`, not `CacaoStoreRequest`, so no validation ever ran; a malformed payload would previously crash with a raw PHP/SQL error instead of a clean 422. The request shape is unchanged: multipart form-data with a `cacao` field containing a JSON string (`label`, `confidence`, `date_analyzed`, `caption`, `uploaderId`) plus a separate `photo` file field. Validation was kept deliberately lenient (no format regex on `confidence`) since the exact mobile payload format couldn't be verified from this repo — only presence/type/existence checks that were already implicitly required (a missing `label` already crashed before; a nonexistent `uploaderId` already violated the FK constraint before).
- **Response envelope** is now `{success, message, data}` — but for the two paginated endpoints (`feed`, `cacaoUploadedByUser`), the *exact* original nested shape is preserved on purpose: `data` holds the full paginator (so `data.data` is the items array, matching what the frontend already reads), and a sibling top-level `meta: {current_page, total_pages, per_page, total}` is kept as its own key (not folded into the paginator's native `last_page` field), because `Feed.vue` and `myprofile.vue` read `response.meta.total_pages` specifically. `store`'s response keeps its original `message` text (`"Cacao entry created successfully!"`) and the `data` key, only adding `success` — safe for the mobile client since it's purely additive.
- **`update`/`destroy` are newly functional** (see above) — no frontend or known mobile caller exists today, so their shape was designed fresh rather than reverse-engineered.
- `recent/upload` and `disease/weeks` wrap their payload in a `cacao` key inside `data` (`{ "data": { "cacao": [...] } }`) rather than putting the array/value directly in `data` — pre-existing shape, unchanged by the refactor, just not previously called out here.
- Heatmap/graph/region-alert logic is unchanged — still delegates to the existing `CacaoHeatmapService`, `WeeklyCacaoHeatmapService`, `CacaoYearGraphService`, `RegionAlertTableDataService` classes (now injected into `CacaoService` rather than the controller directly). These use MySQL-specific raw SQL (`YEAR()`, `DATE_FORMAT()`) — a pre-existing characteristic, not something introduced here.
- `GeocodingService` is unused dead code (confirmed via search) — left untouched, out of scope for this pass.
- **`cacaos` now uses soft deletes.** `destroy` no longer removes the row — it sets `deleted_at`, and default Eloquent queries (`show`, `feed`, `recent`, all analytics/heatmap/graph queries) automatically exclude soft-deleted entries. No restore or trashed-listing endpoint exists yet.
- **Photo dedup + purge (this pass).** Two problems existed: (1) `destroy` soft-deleted the row but *also* unconditionally deleted the R2 file, defeating the point of soft-deleting for a bucket that doubles as ML training data; (2) uploads always re-encoded and stored a new file even for byte-identical photos already on file. Fixed by: adding a nullable/indexed `photo_hash` column (SHA-256 of the raw upload, pre-WebP), branching `POST /cacao` into 5 outcomes on that hash (see below), removing the R2 delete from `destroy`, and adding an admin-only `DELETE /cacao/{uuid}/purge` that hard-deletes the file only when no other non-deleted row still references the same `photo_hash`.
- **`update`/`destroy`/`purge` now route by `uuid`, not `id`.** A predictable integer in a mutating URL is enumerable; `uuid` isn't. Scoped to just these three routes via `{cacao:uuid}` — `show` and every list/analytics endpoint are unaffected and still take integer `id`.

## Response envelope

```json
{ "success": true, "message": "string|null", "data": {} }
```
422 validation failures also include a top-level `errors` object.

## Cacao object shape

| Field | Type |
|---|---|
| id | number |
| uuid | string |
| label | string |
| confidence | string |
| photo | string (URL) |
| caption | string \| null |
| date_analyzed | string |
| uploaderId | number |
| username, profile, city, barangay | present only on list/feed endpoints (joined from the uploader) |
| created_at, updated_at | string |

## POST /cacao

Multipart form-data.

| Field | Type | Rules |
|---|---|---|
| cacao | string (JSON) | required — decoded into `label`, `confidence`, `date_analyzed`, `caption`, `uploaderId` before validation |
| photo | file (image) | required |

Decoded fields:

| Field | Rules |
|---|---|
| label | required, string |
| confidence | required |
| date_analyzed | required, date |
| uploaderId | required, integer, must exist in `users` |
| caption | sometimes, string |

### Photo dedup (new)

`cacaos` has a nullable, indexed `photo_hash` column: a SHA-256 hex digest of the **raw uploaded photo bytes**, computed before any WebP re-encoding. Every `POST /cacao` looks up `photo_hash` (via `withTrashed()`, so soft-deleted rows are included) before doing any encode/store work, and branches into one of four outcomes:

**1. No existing row with this hash** — normal create flow (unchanged): encode to WebP, store to R2, create the row (now also saving `photo_hash`).
Response: `201`, `{ "success": true, "message": "Cacao entry created successfully!", "data": { ...cacao } }`.

**2. Same hash, same `uploaderId`, row not soft-deleted** — you already have this exact photo uploaded and active. No row is created, storage is untouched.
Response: `409`
```json
{
  "success": false,
  "message": "You've already uploaded this photo.",
  "data": { "cacao": { ...full cacao object, same shape as normal responses... } }
}
```

**3. Same hash, same `uploaderId`, row is soft-deleted** — treated as a restore, not a new upload. The existing row's `deleted_at` is cleared and its `label`/`confidence`/`caption`/`date_analyzed` are overwritten with this submission's values. Its `id`/`uuid`/`photo`/`photo_hash` are untouched — the file already exists in R2 and is **not** re-uploaded.
Response: `200`
```json
{
  "success": true,
  "message": "Scan restored.",
  "data": { ...the restored cacao object... }
}
```

**4. Same hash, a *different* `uploaderId`, that row not soft-deleted** — someone else already has this exact photo active. No row is created, storage is untouched. Response is deliberately a reduced field set (no caption/dates of the other user's entry leak):
Response: `409`
```json
{
  "success": false,
  "message": "This photo has already been uploaded by another user.",
  "data": {
    "cacao": {
      "id": 0,
      "uuid": "string",
      "label": "string",
      "confidence": "string",
      "photo": "string (URL)",
      "uploaderId": 0,
      "uploaderUsername": "string"
    }
  }
}
```

**5. Same hash, a different `uploaderId`, that row *is* soft-deleted** — treated as no match. A brand-new row is created for the current uploader (new `id`/`uuid`, current uploader's submitted `label`/`caption`/etc.), but the existing R2 file is reused — `photo` and `photo_hash` are copied onto the new row and the file is not re-uploaded/re-encoded.
Response: same shape/status as outcome 1 (`201`).

## PUT /cacao/{uuid}

| Field | Rules |
|---|---|
| label | sometimes, string |
| caption | sometimes, string |
| date_analyzed | sometimes, date |

The photo cannot be changed on update — `photo` is accepted on create only. Any `photo` sent on this endpoint is ignored.

**Errors** — 403 not owner/admin; 404 not found; 422 validation failure.

## DELETE /cacao/{uuid}

Owner or admin. Soft-deletes the row (`deleted_at` set). **The R2 photo is no longer deleted here** — previously this endpoint permanently destroyed the photo on every delete, which defeated the point of soft-deleting (the photo bucket doubles as an ML training dataset and must not be casually destroyed). Now the file always survives a normal delete; only `purge` (below) can remove it.

Response: `200`, `{ "success": true, "message": "Cacao entry deleted successfully.", "data": null }`.

**Errors** — 403 not owner/admin; 404 not found.

## DELETE /cacao/{uuid}/purge

Admin only (`role:admin` middleware — not owner-or-admin). Route uses `withTrashed()` so it can target an already soft-deleted row directly.

Behavior:
1. If the row isn't already soft-deleted, soft-deletes it (so this doubles as a one-step delete+purge, or a purge-only step for an already-deleted row).
2. Checks whether any *other* non-deleted row shares the same `photo_hash`. If one exists, the R2 file is left alone (still referenced). If none exists, the R2 file is deleted.

Response: `200`
- File removed: `{ "success": true, "message": "Cacao entry and image purged.", "data": null }`
- File retained (still referenced by another entry): `{ "success": true, "message": "Cacao entry deleted; image retained (still referenced by another entry).", "data": null }`

**Errors** — 403 not admin; 404 not found.

## Error status summary

| Status | Meaning |
|---|---|
| 403 | Not the cacao entry's owner and not admin (update/destroy); not admin (purge) |
| 404 | Cacao not found |
| 409 | `POST /cacao` duplicate photo — already uploaded by you (active), or by another user (active) |
| 422 | Validation failure |
