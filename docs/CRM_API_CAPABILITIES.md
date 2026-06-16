# GLS ↔ Wimschool CRM API — Full Capability Reference

> **Purpose of this file:** a complete, self-contained description of what the
> Wimschool External CRM API can do *as currently wired in this codebase*, so an
> external assistant (ChatGPT) can reason about what business questions the data
> can answer. Every endpoint, filter, and returned field below is taken directly
> from the live integration code (`app/Services/Crm/`), not guessed.

---

## 1. Connection basics

| Item | Value |
|------|-------|
| **Base URL (prod)** | `https://app.wimschool.com/school-service` |
| **Base URL (test)** | `https://wimschool-test.wimsaas.com/school-service` |
| **Auth** | `Authorization: Bearer <token>` |
| **Tenant/store scope** | Derived **from the token** — never send tenant/scope in the URL |
| **Per-center tokens** | Each GLS site/center has its own Bearer token (stored on the `sites` table). One token can be ALL_STORES or SELECTED_STORES |
| **Rate limit** | **60 requests / minute** → HTTP `429` with `details.retryAfterSeconds` |
| **Response cache** | GET responses cached locally for `CRM_API_CACHE_TTL` (default 60s) |
| **All methods are GET** | The API is **read-only** from our side. No writes, no mutations |

### Pagination (every transactional endpoint)
Zero-based cursor pagination:

| Param | Default | Notes |
|-------|---------|-------|
| `page` | 0 | Zero-based |
| `size` | 10 | Max **25** (standard endpoints), max **500** (`/bulk/*`) |
| `includeTotal` | false | Set `true` only when you need `totalElements` / `totalPages` (adds server cost) |

Response envelope:
```json
{
  "success": true,
  "data": [ ... rows ... ],
  "pagination": { "page": 0, "size": 25, "returned": 12, "hasMore": false,
                  "totalElements": 12, "totalPages": 1 },
  "meta": { "apiVersion": "...", "requestId": "...", "timestamp": "2026-06-03T15:00:00Z" }
}
```
`totalElements` / `totalPages` appear **only** when `includeTotal=true`. Otherwise loop on `hasMore`.

### Important data-quality rules (learned the hard way)
- **`SESSION_DATE` is UTC.** Evening Casablanca (UTC+1) sessions fall on the *previous*
  calendar day if not converted. Always `setTimezone('Africa/Casablanca')` before date math.
- **`/payment-allocations` is the ONLY endpoint** that links `STUDENT_ID` + `CLASS_ID` +
  payment date together. `/payments` has no `classId` filter; `/registrations` has no payment dates.
- **Registration status timestamps can be edited months later** — never use them as a payment-date proxy.
- **Use `/bulk/*` for any sync over ~100 records** (500/page vs 25/page = 20× fewer calls).

---

## 2. Endpoint catalog (what each one tells you)

Legend: **Std** = standard (size ≤ 25), **Bulk** = `/bulk/...` (size ≤ 500), **LOV** = reference list (no paging, `limit` only).

### 2.1 Students — *who the students are*
- **Std:** `GET /api/external/v1/students`
- **Bulk:** `GET /api/external/v1/bulk/students`
- **LOV lookup:** `GET /api/external/v1/lov/students` (paginated despite being under `/lov/`)

**Filters:** `strStoreId`, `reference`, `firstName`, `lastName`, `phoneNumber`, `sexe`,
`categoryId`, `registrationStatus`.

**Answers:** student roster, contact info, gender split, how many active vs archived,
search/autocomplete a student by name/phone/reference.

---

### 2.2 Groups / Classes — *what courses exist and who's in them*
- **Std:** `GET /api/external/v1/groups/classes`
- **Bulk:** `GET /api/external/v1/bulk/groups/classes`

**Filters:** `strStoreId`, `schoolYearId`, `schoolDepartmentId`, `schoolStageId`,
`schoolLevelId`, `employeeTeacherId`, `statusId`, `history` (`"Y"` includes historical groups, `"N"` active only).

**Returned fields:** `ID`, `CLASS_ID`, `REFERENCE`, `NAME`, `NAME_AR`, `ACTIVE`,
`START_DATE`, `END_DATE`, `SCHOOL_LEVEL_NAME`, `EMPLOYEE_TEACHER_FULL_NAME`,
`CLASSIFICATION_NAME`, `STATUS_NAME`, `LIST_STUDENT` (JSON string),
`CLASS_COUNT_STUDENTS_ACTIVE`, `LIST_STUDENT_ACTIVE`,
`CLASS_COUNT_STUDENTS_ARCHIVED`, `LIST_STUDENT_ARCHIVED`,
`CLASS_COUNT_STUDENTS_CANCLED`, `LIST_STUDENT_CANCELED`,
`SERVICE_LIST` (JSON string), `SCHOOL_YEAR_ID`, `STR_STORE_ID`.

**Answers:** all groups + their level, teacher, classification, start/end dates, and
**live headcount** (active / archived / cancelled) per group. The student lists let you
build group → student rosters and track group fill rate and evolution over time.

#### Level sessions
- **Std:** `GET /api/external/v1/groups/level-sessions`
- **Bulk:** `GET /api/external/v1/bulk/groups/level-sessions`
- **Filters:** `strStoreId`, `schoolYearId` (+ extra passthrough).
- **Answers:** the level-session layer that groups/packages hang off of (used to join packages → sessions).

---

### 2.3 Registrations — *who enrolled, in what, when*
- **Std:** `GET /api/external/v1/registrations`
- **Bulk:** `GET /api/external/v1/bulk/registrations`

**Filters:** `strStoreId`, `schoolYearId`, `reference`, `studentId`,
`registrationStatusId`, `levelSessionId`, `levelSessionPackageIds` (CSV),
`startDate`, `endDate`, `filterStatus`. (Bulk also accepts `classId`.)

**Answers:** enrollment records, registration status (active/archived/cancelled),
which package/level-session a student registered into, enrollment date ranges.
⚠️ No payment dates here — use payment-allocations for money+class linkage.

---

### 2.4 Payments — *the money received*
- **Std:** `GET /api/external/v1/payments`
- **Bulk:** `GET /api/external/v1/bulk/payments`

**Filters:** `strStoreId`, `schoolYearId`, `reference`, `studentId`, `paymentTypeId`,
`paymentStatusId`, `paymentMethodeId` *(API keeps the typo "Methode" on std;
bulk uses `paymentMethodId`)*, `startDate`, `endDate`. (Bulk also: `cashBoxId`.)

**Answers:** all payments in a date range by student, type, method, status, cash box.
⚠️ **No `classId` filter** — cannot attribute revenue to a specific group from here alone.

---

### 2.5 Payment Allocations — *the money, linked to a class* ⭐
- **Std:** `GET /api/external/v1/payment-allocations`
- **Bulk:** `GET /api/external/v1/bulk/payment-allocations`

**Filters:** `strStoreId`, `schoolYearId`, `classId`, `levelSessionId`,
`registrationId`, `studentId`, `effectiveDatePaymentAllocation`, `cashBoxId`,
`startDate`, `endDate` (start/end must be paired).

**Returned fields:** `ID`, `AMOUNT`, `PAYMENT_ID`, `PAYMENT_REFERENCE`,
`STUDENT_ID`, `STUDENT_FULL_NAME`, `REGISTRATION_ID`, `SERVICE_TYPE_NAME`
(e.g. "Inscription", "Mensualité"), `EFFECTIVE_DATE_PAYMENT`,
`EFFECTIVE_DATE_PAYMENT_ALLOCATION`.

**Answers:** ⭐ **revenue per group/class**, revenue per student per service type,
inscription vs monthly-fee breakdown, payment timing — the single source that ties
**money ↔ student ↔ class ↔ date** together. This is the backbone of all CA (revenue) reports.

---

### 2.6 Payment Collection / Receivables (créances) — *who still owes*
- **Std:** `GET /api/external/v1/payment-collection`
- **Bulk:** `GET /api/external/v1/bulk/payment-collection`

**Filters:** `strStoreId`, `schoolYearId`, `id`, `registrationId`,
`registrationStatusId`, `subscriptionServiceId`, `prestationId`, `prestationTypeId`,
`orderId`, `studentId`, `classId`, `levelSessionId`, `levelSessionPackageId`,
`levelSessionPackageIds` (CSV), `serviceTypeIds` (CSV), `dueDateStartDate`,
`dueDateEndDate`, `startDay` (min delay days), `endDay` (max delay days).

**Answers:** outstanding balances / unpaid receivables, aging (via `startDay`/`endDay`
delay window), who owes for which class/service, due-date buckets. Drives the unpaid-student
and recouvrement (debt-collection) workflows.

---

### 2.7 Payment Checks (chèques) — *cheque tracking*
- **Std:** `GET /api/external/v1/payment-checks`

**Filters:** `strStoreId`, `schoolYearId`, `checkNumber`, `studentId`,
`paymentCheckStatusId`, `bankId`, `dueDateMin`, `dueDateMax`.

**Answers:** cheques on file, their status (pending/cashed/bounced via status LOV),
bank, due dates — upcoming cheque cash-ins and bounced-cheque follow-up.

---

### 2.8 Subscription Services — *what each student is billed for*
- **Std:** `GET /api/external/v1/subscription-services`

**Filters:** `strStoreId`, `schoolYearId`, `id`, `studentId`, `registrationId`,
`dueDate` (returns services with due date ≤ this), `levelSessionPackageId`,
`subscriptionServiceStatusId`, `levelSessionId`.

**Returned fields:** `ID`, `REFERENCE`, `STUDENT_ID`, `REGISTRATION_ID`,
`TOTAL_PRICE`, `REST_AMOUNT`, `DUE_DATE`, `SERVICE_TYPE_NAME`.

**Answers:** per-student billing lines — total price vs remaining amount (`REST_AMOUNT`),
due dates, service type. Lets you compute expected revenue and outstanding per student/service.

---

### 2.9 Session Presence (attendance) — *who showed up*
- **Std:** `GET /api/external/v1/session-presence`
- **Bulk:** `GET /api/external/v1/bulk/session-presence`

**Std filters:** `strStoreId`, `schoolYearId`, `date` (single ISO day), `presence`
(`"Y"`), `absence` (`"Y"`), `presenceStatus` (1=present, 0=absent), `classId`,
`levelSessionId`, `sessionId`, `registrationId`, `studentId`.
**Bulk filters:** `dateFrom`, `dateTo`, `classId`, `strStoreId` (+ extra).

**Returned fields:** `STUDENT_ID`, `FIRST_NAME`, `LAST_NAME`, `SESSION_ID`,
`SESSION_DATE` *(UTC!)*, `PRESENCE` ("Y"/"N"), `ABSENCE` ("Y"/"N"),
`PRESENCE_STATUS` (1=present, 0=absent).

**Answers:** attendance per student/class/session/date, absence rates, attendance
trends, sessions held per group (used to compute teacher pay from sessions taught and
to flag at-risk/churning students by absence).

> ⚠️ Std endpoint only accepts a **single `date`** — to scan a range you fan out one
> request per day (`parallelFetch`). Use the **bulk** endpoint with `dateFrom`/`dateTo` for ranges.

---

### 2.10 Employees & Salaries — *staff and pay*
- **Employees:** `GET /api/external/v1/employees` — filters: `strStoreId`. (size ≤ 500)
- **Salaries:** `GET /api/external/v1/salaries` — filters: `strStoreId`, `startDate`,
  `endDate`, `employeeId`. (size ≤ 500)
- **Calculated salary per class:** `GET /api/external/v1/employee-calculated-salary-classes`
  - Filters: `strStoreId`, `employeeId`, `classId`, `operationMonth` (1–12), `operationYear`.
  - Returned fields: `PK`, `EMPLOYEE_ID`, `FIRST_NAME`, `LAST_NAME`, `CLASS_ID`,
    `OPERATION_YEAR`, `OPERATION_MONTH`, `MONTHLY_SALARY`, `REMAINING_AMOUNT`.
- **Bulk salaries:** `GET /api/external/v1/bulk/employee-calculated-salary-classes`

**Answers:** staff roster, salary records by period, and **teacher cost per class per
month** (`MONTHLY_SALARY`, `REMAINING_AMOUNT`) — the input for prof-payment automation
and group profitability (revenue per class − teacher cost per class).

---

### 2.11 Expenses — *money going out*
- **Std:** `GET /api/external/v1/expenses` (size ≤ 25)

**Filters:** `strStoreId`, `schoolYearId`, `id`, `expenseStatusId`, `expenseTypeId`,
`cashBoxId`, `levelSessionId`, `paymentMethodId`, `reference` (contains),
`invoiceReference` (contains), `startDate`, `endDate`.

**Answers:** operating expenses by type/status/cash box/date — the cost side of the
ledger for net margin per center.

---

## 3. LOV reference endpoints (dropdown / ID lookups)

No pagination — `limit` only (default 100). Required scope `lov:read`. Use these to
translate the numeric IDs above into human labels.

| Endpoint | Gives you |
|----------|-----------|
| `GET /api/external/v1/lov/school-levels` | School levels (filters: `strStoreId`, `schoolDepartmentId`, `schoolStageId`, `active`) |
| `GET /api/external/v1/lov/registration-statuses` | Registration status labels |
| `GET /api/external/v1/lov/registration-conventions` | Registration conventions (filter: `strStoreId`) |
| `GET /api/external/v1/lov/registration-change-status-reasons` | Why a registration changed status |
| `GET /api/external/v1/lov/payment-types` | Payment type labels (Inscription, Mensualité, …) |
| `GET /api/external/v1/lov/payment-statuses` | Payment status labels |
| `GET /api/external/v1/lov/payment-methods` | Payment method labels (cash, cheque, …) |
| `GET /api/external/v1/lov/payment-check-statuses` | Cheque status labels |
| `GET /api/external/v1/lov/level-session-packages` | Packages (filters: `strStoreId`, `levelSessionId`) |
| `GET /api/external/v1/lov/categories` | Student/registration categories |
| `GET /api/external/v1/lov/expense-types` | Expense type labels |
| `GET /api/external/v1/lov/banks` | Bank list |

---

## 4. What you can actually compute (business questions → endpoints)

| Question | Endpoint(s) to combine |
|----------|------------------------|
| Total revenue (CA) for a center / month | `payment-allocations` (sum `AMOUNT` over date range) |
| Revenue **per group / class** | `payment-allocations` filtered by `classId` |
| Inscription vs monthly-fee revenue split | `payment-allocations` grouped by `SERVICE_TYPE_NAME` |
| Who still owes money (créances) + aging | `payment-collection` (`startDay`/`endDay`, `dueDate*`) |
| Expected vs collected per student | `subscription-services` (`TOTAL_PRICE` − `REST_AMOUNT`) |
| Group headcount & fill rate | `groups/classes` (`CLASS_COUNT_STUDENTS_ACTIVE`, lists) |
| New enrollments in a period | `registrations` (`startDate`/`endDate`) |
| Attendance / absence rate per student or class | `session-presence` (`PRESENCE_STATUS`) |
| Churn signals (students going absent) | `session-presence` + `registrations` status |
| Teacher cost per class per month | `employee-calculated-salary-classes` |
| Group profitability | `payment-allocations` (revenue) − `employee-calculated-salary-classes` (cost) per `classId` |
| Net margin per center | revenue (`payment-allocations`) − `salaries` − `expenses` |
| Upcoming / bounced cheques | `payment-checks` (`dueDateMin/Max`, status LOV) |

---

## 5. Hard limits & gotchas (give these to any consumer)

1. **Read-only.** No endpoint writes back to Wimschool. All sync is one-way pull.
2. **60 req/min.** Batch with `/bulk/*` and `size=500`; expect `429` + `retryAfterSeconds`.
3. **`includeTotal=false`** on every page except when you truly need a count.
4. **`SESSION_DATE` is UTC** — convert to `Africa/Casablanca` before any date grouping.
5. **`payment-allocations` is the only money↔class↔student↔date join.** Don't try to get per-class revenue from `/payments`.
6. **Registration status timestamps are mutable** — not a reliable payment-date proxy.
7. **`strStoreId` is optional**: omit for ALL_STORES tokens, required for SELECTED_STORES tokens.
8. **Some list fields are JSON strings** (`LIST_STUDENT`, `SERVICE_LIST`) — decode before use.
9. **Param typo:** standard `/payments` uses `paymentMethodeId`; bulk uses `paymentMethodId`.

---

## 6. Error codes

| HTTP | `errorCode` | Meaning |
|------|-------------|---------|
| 400 | `VALIDATION_ERROR` | Bad request parameters |
| 401 | `AUTH_INVALID_TOKEN` | Token missing, expired, disabled, or revoked |
| 403 | `AUTH_SCOPE_DENIED` | Token valid but missing required scope |
| 429 | `TOKEN_RATE_LIMIT_PER_MINUTE_EXCEEDED` | 60 req/min exceeded — see `details.retryAfterSeconds` |
| 500 | — | Unexpected server error |
