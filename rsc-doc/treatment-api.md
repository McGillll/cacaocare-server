# Treatment API

Base path: `/api`. A Treatment is a disease-treatment library entry with a nested list of `keypoints` (step-by-step instructions). Keypoints have no standalone endpoint — they only ever exist nested inside a Treatment's create/update payload, replaced wholesale on each write.

## Endpoints overview

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/treatment` | none (public) | List all treatments with their keypoints |
| GET | `/treatment/{id}` | any authenticated user | Get one treatment with its keypoints |
| POST | `/treatment` | role: admin | Create a treatment (optionally with keypoints) |
| PUT | `/treatment/{uuid}` | role: admin | Update a treatment (replaces keypoints wholesale if provided) |
| DELETE | `/treatment/{uuid}` | role: admin | Delete a treatment (keypoints cascade-delete via FK) |

`show` and `index` still route by integer `id`. `update` and `destroy` route by `uuid` instead — a
sequential integer in a mutating endpoint's URL is guessable/enumerable, so those two now take the
non-sequential public identifier. This is scoped per-route (`Route::put('treatment/{treatment:uuid}', ...)`
etc. in `routes/api/treatment.php`), not a model-wide change — `show`/`index` deliberately keep resolving
by `id`. `treatment_key_points` has no standalone routes and was not touched.

## Who can do what

- **Public**: `GET /treatment` (index) — the user-facing treatment guide reads this without login.
- **Any authenticated user**: `show`.
- **Admin only** (`role:admin` middleware): `store`, `update`, `destroy` — opened up from the original code (which let any authenticated user manage treatments) since only the admin panel (`TreatmentLibrary.vue`) uses these.

## Response envelope

```json
{ "success": true, "message": "string|null", "data": {} }
```
422 validation failures also include a top-level `errors` object.

## Treatment object shape

| Field | Type |
|---|---|
| id | number |
| uuid | string |
| disease | string (unique) |
| short_description | string \| null |
| img_url | string \| null |
| keypoints | array of `{ id, disease_id, title, detailed_step, created_at, updated_at }` |
| created_at | string |
| updated_at | string |

## POST /treatment

JSON body.

| Field | Type | Rules |
|---|---|---|
| disease | string | required, unique |
| short_description | string | sometimes |
| img_url | string | sometimes, url |
| keypoints | array | sometimes — `[{ "title": string, "detailed_step": string }]` |

## PUT /treatment/{uuid}

Multipart (`POST` transport with `_method=PUT` spoofing, matching the frontend's existing FormData submission). `keypoints` may arrive either as a real array (JSON body) or as a JSON-encoded string (FormData field) — both are accepted and normalized server-side.

| Field | Type | Rules |
|---|---|---|
| disease | string | sometimes, unique (ignoring the current row) |
| short_description | string | sometimes |
| img_url | string | sometimes, url |
| keypoints | array \| JSON string | sometimes — if present, **replaces all existing keypoints** for this treatment (delete-then-recreate, not a merge) |

**Errors** — 403 not admin; 404 not found; 422 validation failure (including malformed `keypoints` JSON string, and duplicate `disease`, newly enforced — previously a duplicate disease caused an unhandled SQL error).

## Error status summary

| Status | Meaning |
|---|---|
| 403 | Not an admin (store/update/destroy) |
| 404 | Treatment not found |
| 422 | Validation failure (including duplicate disease or malformed keypoints JSON) |
