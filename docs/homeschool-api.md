# Homeschool External API v1

**Base URL:** `https://app.wimschool.com/school-service`  
**Auth:** `Authorization: Bearer <token>`  
**Rate limit:** 60 req/min → 429 with `retryAfterSeconds`

---

## Pagination

All transactional endpoints use zero-based cursor pagination.

**Request params:**

| Param | Type | Default | Notes |
|-------|------|---------|-------|
| `page` | integer | 0 | Zero-based. No hard max — avoid deep paging. |
| `size` | integer | 10 | Max **25** (standard), max **500** (bulk endpoints) |
| `includeTotal` | boolean | false | Set `true` only when you need `totalElements`/`totalPages` |

**Response envelope:**

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "page": 0,
    "size": 25,
    "returned": 12,
    "hasMore": false,
    "totalElements": 12,
    "totalPages": 1
  },
  "meta": {
    "apiVersion": "string",
    "requestId": "string",
    "timestamp": "2026-06-03T15:00:00Z"
  }
}
```

> `totalElements` and `totalPages` are **only present** when `includeTotal=true`.  
> Use `hasMore` to drive pagination loops — no `includeTotal` needed.

**Recommended loop pattern:**

```php
$page = 0;
$result = [];

while (true) {
    $response = $client->get('/api/external/v1/bulk/payment-allocations', [
        'page' => $page,
        'size' => 500,
        'includeTotal' => 'false',
        // ...other filters
    ]);

    $rows = $response['data'] ?? [];
    if (empty($rows)) break;

    array_push($result, ...$rows);

    if (!($response['pagination']['hasMore'] ?? false)) break;
    $page++;
}
```

---

## Standard Endpoints (size max = 25)

### Groups

#### `GET /api/external/v1/groups/classes`
List classes/groups.

| Param | Type | Description |
|-------|------|-------------|
| `strStoreId` | int64 | Filter by store |
| `schoolYearId` | int64 | Filter by school year |
| `page`, `size`, `includeTotal` | — | Pagination |

**Key response fields per record:**

| Field | Description |
|-------|-------------|
| `CLASS_ID` | Unique class identifier |
| `NAME` | Class name |
| `REFERENCE` | Class reference code |
| `START_DATE` | Class start date |
| `END_DATE` | Class end date |
| `CLASS_COUNT_STUDENTS_ACTIVE` | Current active student count |
| `LIST_STUDENT_ACTIVE` | Array of active student objects |
| `LIST_STUDENT_ARCHIVED` | Array of archived student objects |

---

#### `GET /api/external/v1/groups/level-sessions`
List level sessions.

---

### Payment Allocations

#### `GET /api/external/v1/payment-allocations`
List payment allocations. **Best endpoint for linking payments to specific classes.**

| Param | Type | Description |
|-------|------|-------------|
| `strStoreId` | int64 | Filter by store |
| `startDate` | date | Start of range |
| `endDate` | date | End of range |
| `studentId` | any | Filter by student |
| `page`, `size`, `includeTotal` | — | Pagination |

**Key response fields:**

| Field | Description |
|-------|-------------|
| `STUDENT_ID` | Student identifier |
| `CLASS_ID` | Class the payment was allocated to |
| `SERVICE_TYPE_NAME` | e.g. "Inscription", "Mensualité" |
| `EFFECTIVE_DATE_PAYMENT_ALLOCATION` | Allocation date |
| `EFFECTIVE_DATE_PAYMENT` | Actual payment date |

> This is the only endpoint that exposes both `STUDENT_ID` + `CLASS_ID` + payment date together.  
> `/payments` has no `classId` filter. `/registrations` has no payment dates.

---

### Payments

#### `GET /api/external/v1/payments`
List payments.

| Param | Type | Description |
|-------|------|-------------|
| `strStoreId` | int64 | Filter by store |
| `schoolYearId` | int64 | Filter by school year |
| `studentId` | any | Filter by student |
| `paymentTypeId` | any | Filter by type |
| `paymentStatusId` | any | Filter by status |
| `paymentMethodeId` | any | Filter by method |
| `startDate` | date | Start of range |
| `endDate` | date | End of range |
| `page`, `size`, `includeTotal` | — | Pagination |

> **No `classId` filter** — cannot isolate payments by group from this endpoint alone.

---

### Payment Collection / Receivables

#### `GET /api/external/v1/payment-collection`
List payment collection (receivables / créances).

#### `GET /api/external/v1/payment-checks`
List payment checks (chèques).

---

### Registrations

#### `GET /api/external/v1/registrations`
List registrations.

> Status timestamps may be edited months after the fact — do not use as payment date proxy.

---

### Session Presence

#### `GET /api/external/v1/session-presence`
List student session presence.

| Param | Type | Description |
|-------|------|-------------|
| `strStoreId` | int64 | Filter by store |
| `startDate` | date | Start of range |
| `endDate` | date | End of range |
| `studentId` | any | Filter by student |
| `classId` | any | Filter by class |
| `page`, `size`, `includeTotal` | — | Pagination |

**Key fields:** `STUDENT_ID`, `SESSION_DATE`

> `SESSION_DATE` is emitted in **UTC**. Evening sessions in Casablanca (UTC+1) fall on the previous calendar day if not converted. Always use `setTimezone('Africa/Casablanca')`.

---

### Students

#### `GET /api/external/v1/students`
List students.

---

### Subscription Services

#### `GET /api/external/v1/subscription-services`
List subscription services.

---

### Employee Salaries

#### `GET /api/external/v1/employee-calculated-salary-classes`
List employee calculated salary records per class.

---

## Bulk Endpoints (size max = 500)

Same filters, auth, scopes, and rate limits as standard endpoints.  
Use for large syncs — 20× fewer HTTP calls than standard (500 vs 25 per page).

| Endpoint | Description |
|----------|-------------|
| `GET /api/external/v1/bulk/groups/classes` | Bulk classes |
| `GET /api/external/v1/bulk/groups/level-sessions` | Bulk level sessions |
| `GET /api/external/v1/bulk/payment-allocations` | Bulk payment allocations |
| `GET /api/external/v1/bulk/payments` | Bulk payments |
| `GET /api/external/v1/bulk/payment-collection` | Bulk receivables |
| `GET /api/external/v1/bulk/payment-checks` | Bulk checks |
| `GET /api/external/v1/bulk/registrations` | Bulk registrations |
| `GET /api/external/v1/bulk/session-presence` | Bulk session presence |
| `GET /api/external/v1/bulk/students` | Bulk students |
| `GET /api/external/v1/bulk/subscription-services` | Bulk subscription services |
| `GET /api/external/v1/bulk/employee-calculated-salary-classes` | Bulk salaries |

---

## LOV Endpoints (reference lists)

Safety-limited — no deep paging needed. Use for dropdowns and filter options.

| Endpoint | Description |
|----------|-------------|
| `GET /api/external/v1/lov/students` | Search students |
| `GET /api/external/v1/lov/school-levels` | School levels |
| `GET /api/external/v1/lov/registration-statuses` | Registration statuses |
| `GET /api/external/v1/lov/registration-conventions` | Registration conventions |
| `GET /api/external/v1/lov/registration-change-status-reasons` | Change status reasons |
| `GET /api/external/v1/lov/payment-types` | Payment types |
| `GET /api/external/v1/lov/payment-statuses` | Payment statuses |
| `GET /api/external/v1/lov/payment-methods` | Payment methods |
| `GET /api/external/v1/lov/payment-check-statuses` | Check statuses |
| `GET /api/external/v1/lov/level-session-packages` | Level session packages |
| `GET /api/external/v1/lov/categories` | Categories |
| `GET /api/external/v1/lov/banks` | Banks |

---

## Error Codes

| HTTP | `errorCode` | Meaning |
|------|-------------|---------|
| 400 | `VALIDATION_ERROR` | Bad request parameters |
| 401 | `AUTH_INVALID_TOKEN` | Token missing, expired, disabled, or revoked |
| 403 | `AUTH_SCOPE_DENIED` | Token valid but missing required scope |
| 429 | `TOKEN_RATE_LIMIT_PER_MINUTE_EXCEEDED` | 60 req/min exceeded — check `details.retryAfterSeconds` |
| 500 | — | Unexpected server error |

---

## Key Design Rules

1. **Never send tenant/store/scope in the token derivation fields** — the server derives them from the Bearer token automatically.
2. **`strStoreId` is optional** — omit it for ALL_STORES tokens; include it for SELECTED_STORES tokens.
3. **Use bulk endpoints for any sync fetching more than ~100 records** — standard max is 25/page.
4. **`includeTotal=false` on every page except when you explicitly need the count** — it adds server overhead.
5. **`SESSION_DATE` is UTC** — always convert to `Africa/Casablanca` before date comparisons.
6. **`/payment-allocations` is the only source** that links `STUDENT_ID` + `CLASS_ID` + payment date in one record.
