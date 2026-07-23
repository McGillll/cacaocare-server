# Comment API

Base path: `/api`. Comments are scoped to a Cacao entry.

## Endpoints overview

| Method | Path | Auth | Description |
|---|---|---|---|
| GET | `/cacaos/{cacao}/comments` | any authenticated user | Paginated list of comments (10/page) |
| POST | `/cacaos/{cacao}/comments` | any authenticated user | Create a comment on a cacao entry |
| PUT | `/comments/{id}` | owner or admin | Update a comment |
| DELETE | `/comments/{id}` | owner or admin | Delete a comment |

Identifier stays integer `id` (no uuid).

## Who can do what

- **Any authenticated user**: `index`, `store`.
- **Owner-or-admin** (checked in `CommentService`, not middleware — needs the resolved comment's `user_id`): `update`, `destroy`. A non-owner, non-admin request throws `UnauthorizedActionException` → 403.
- **Newly enforced**: a `CommentPolicy` already existed with this exact owner-or-admin rule but was never invoked anywhere in the old code — any authenticated user could edit/delete any comment via a direct API call, even though the UI only showed Edit/Delete buttons for your own comments. This gap is now closed at the service layer.

## Response envelope — fully wrapped

```json
{ "success": true, "message": "string|null", "data": {} }
```
422 validation failures also include a top-level `errors` object.

**This is a deliberate consistency change from the original code**, which returned inconsistent raw shapes per endpoint (`index` returned the raw Laravel paginator with no wrapper at all; `store` returned the bare comment object; `update` was already wrapped in `{data: ...}`; `destroy` returned an empty 204). All four now use the same envelope — see `client/rsc-doc/frontend-flag.md` (FLAG 2) for what the frontend needs to change.

## Comment object shape

| Field | Type |
|---|---|
| id | number |
| body | string |
| created_at | string |
| user | `{ id, username, profile }` |

## GET /cacaos/{cacao}/comments

`data` is the full Laravel paginator shape (not re-nested): `{ current_page, data: [Comment], first_page_url, last_page, per_page, total, ... }`. `data.data` is the array of comments (each shaped per `CommentResource`); pagination fields (`data.last_page`, etc.) sit alongside it.

Query param: `?page=N`.

## POST /cacaos/{cacao}/comments

| Field | Type | Rules |
|---|---|---|
| body | string | required |

**Success** — 201, `data` = the created comment (with `user`).

## PUT /comments/{id}

| Field | Type | Rules |
|---|---|---|
| body | string | sometimes, required if present |

**Errors** — 403 not owner/admin; 404 not found; 422 validation failure.

## DELETE /comments/{id}

**Success** — 200 (previously 204 with no body) — `{ success: true, message: "...", data: null }`.

**Errors** — 403 not owner/admin; 404 not found.

## Error status summary

| Status | Meaning |
|---|---|
| 403 | Not the comment's owner and not admin |
| 404 | Comment or cacao not found |
| 422 | Validation failure |
