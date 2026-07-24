# Cacao API

Base path: `/api`. A Cacao entry is an AI-classified pod scan (label = disease/health status, confidence, photo, uploader). This is the largest module — it also aggregates heatmap/graph/region-alert data used by the admin dashboard.

**Note on other clients:** `store` (and, less certainly, `show`/`update`/`destroy`) may be used by a mobile app not present in this repo. Their request/response shapes were preserved deliberately during this refactor — see call-outs below.

## Endpoints overview

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/cacao` | any authenticated user | Create a cacao entry (mobile upload) |
| GET | `/cacao/feed/{order}/{filter}` | any authenticated user | Paginated feed (6/page), `?username=` search |
| GET | `/cacao/{id}` | any authenticated user | Get one entry |
| PUT | `/cacao/{id}` | owner or admin | Update an entry |
| DELETE | `/cacao/{id}` | owner or admin | Delete an entry |
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

Identifier stays integer `id` (no uuid).

## Who can do what

- **Any authenticated user**: everything except `update`/`destroy`.
- **Owner-or-admin** (checked in `CacaoService`): `update`, `destroy` — newly implemented; previously `update` didn't exist as a method at all (calling the registered route would 500) and `destroy` was an empty no-op. Neither had a known caller, but both are now real, gated the same way as Comment/User.

## What changed vs. the original code (and why)

- **Fixed a real bug in `show`**: the old code did `Cacao::where('uploaderId', $id)->first()` — looking up by *uploader* id, not the entry's own id, so it returned some arbitrary cacao belonging to that user rather than the requested one. Route-model binding now resolves the correct entry by its own id.
- **`store` validation is now actually wired.** The old `CacaoStoreRequest` class existed but was never used — the controller was type-hinted `Request $request`, not `CacaoStoreRequest`, so no validation ever ran; a malformed payload would previously crash with a raw PHP/SQL error instead of a clean 422. The request shape is unchanged: multipart form-data with a `cacao` field containing a JSON string (`label`, `confidence`, `date_analyzed`, `caption`, `uploaderId`) plus a separate `photo` file field. Validation was kept deliberately lenient (no format regex on `confidence`) since the exact mobile payload format couldn't be verified from this repo — only presence/type/existence checks that were already implicitly required (a missing `label` already crashed before; a nonexistent `uploaderId` already violated the FK constraint before).
- **Response envelope** is now `{success, message, data}` — but for the two paginated endpoints (`feed`, `cacaoUploadedByUser`), the *exact* original nested shape is preserved on purpose: `data` holds the full paginator (so `data.data` is the items array, matching what the frontend already reads), and a sibling top-level `meta: {current_page, total_pages, per_page, total}` is kept as its own key (not folded into the paginator's native `last_page` field), because `Feed.vue` and `myprofile.vue` read `response.meta.total_pages` specifically. `store`'s response keeps its original `message` text (`"Cacao entry created successfully!"`) and the `data` key, only adding `success` — safe for the mobile client since it's purely additive.
- **`update`/`destroy` are newly functional** (see above) — no frontend or known mobile caller exists today, so their shape was designed fresh rather than reverse-engineered.
- `recent/upload` and `disease/weeks` wrap their payload in a `cacao` key inside `data` (`{ "data": { "cacao": [...] } }`) rather than putting the array/value directly in `data` — pre-existing shape, unchanged by the refactor, just not previously called out here.
- Heatmap/graph/region-alert logic is unchanged — still delegates to the existing `CacaoHeatmapService`, `WeeklyCacaoHeatmapService`, `CacaoYearGraphService`, `RegionAlertTableDataService` classes (now injected into `CacaoService` rather than the controller directly). These use MySQL-specific raw SQL (`YEAR()`, `DATE_FORMAT()`) — a pre-existing characteristic, not something introduced here.
- `GeocodingService` is unused dead code (confirmed via search) — left untouched, out of scope for this pass.

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

## PUT /cacao/{id}

Multipart (POST transport + `_method=PUT`, per file-upload convention).

| Field | Rules |
|---|---|
| label | sometimes, string |
| caption | sometimes, string |
| date_analyzed | sometimes, date |
| photo | sometimes, image |

**Errors** — 403 not owner/admin; 404 not found; 422 validation failure.

## Error status summary

| Status | Meaning |
|---|---|
| 403 | Not the cacao entry's owner and not admin (update/destroy) |
| 404 | Cacao not found |
| 422 | Validation failure |
