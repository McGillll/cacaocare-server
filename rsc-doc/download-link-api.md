# Download Link API

Base path: `/api`. Represents the app's downloadable release links (e.g. an installer/APK link + version).

## Endpoints overview

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/download/latest` | none | Get the most recently created download link |
| GET | `/download/latest/date` | any authenticated user | Get just the `created_at` of the most recent link |
| GET | `/download_link` | any authenticated user | List all download links |
| GET | `/download_link/{id}` | any authenticated user | Get one download link |
| POST | `/download_link` | role: admin | Create a download link |
| PUT | `/download_link/{uuid}` | role: admin | Update a download link |
| DELETE | `/download_link/{uuid}` | role: admin | Delete a download link |

`show`/`index` still bind on the internal integer `id`. `update`/`destroy` now bind on `uuid`, matching the pattern used by `Cacao`/`User`.

## Who can do what

- **Public**: `GET /download/latest` (used to show a public download banner without login).
- **Any authenticated user**: `latestDate`, `index`, `show`.
- **Admin only** (`role:admin` middleware): `store`, `update`, `destroy` — this was opened up from the original code (which let any authenticated user manage links) since only the admin panel (`AppUpdates.vue`) actually uses these.

## Response envelope

```json
{ "success": true, "message": "string|null", "data": {} }
```
422 validation failures also include a top-level `errors` object.

## Download link object shape

| Field | Type |
|---|---|
| id | number |
| uuid | string |
| download_link | string (URL) |
| version | string (unique) |
| description | string \| null |
| created_at | string |
| updated_at | string |

## POST /download_link

| Field | Type | Rules |
|---|---|---|
| download_link | string | required |
| version | string | required, unique |
| description | string | sometimes |

**Errors** — 403 not admin; 422 validation failure (including duplicate `version`, newly enforced — previously a duplicate version caused an unhandled SQL error instead of a clean validation message).

## PUT /download_link/{uuid}

| Field | Type | Rules |
|---|---|---|
| download_link | string | sometimes |
| version | string | sometimes, unique (ignoring the current row) |
| description | string | sometimes |

**Errors** — 403 not admin; 404 not found; 422 validation failure.

## What changed (2026-07-27)

- `download_links` gained a `uuid` column (backfilled for existing rows, then made `NOT NULL UNIQUE`). New rows get one automatically via the `UsesUuid` trait.
- `PUT /download_link/{id}` and `DELETE /download_link/{id}` became `PUT /download_link/{uuid}` and `DELETE /download_link/{uuid}` — implicit route-model binding now resolves via `uuid` for these two routes only. `GET /download_link/{id}` and `GET /download_link` are unchanged.

## Error status summary

| Status | Meaning |
|---|---|
| 403 | Not an admin (store/update/destroy) |
| 404 | Download link not found |
| 422 | Validation failure (including duplicate version) |
