# Wimschool CRM API â€” Complete Guide

> **Base URL:** `CRM_API_BASE_URL` (set in `.env`)  
> **Auth:** Bearer token (`CRM_API_TOKEN` in `.env`, or per-center token stored on `sites.crm_token`)  
> **Version:** External API v1 (`/api/external/v1/...`)  
> **Cache:** All GET calls are Laravel-cached for `CRM_API_CACHE_TTL` seconds (default 60s)

---

## Entry Point

```php
$crm = app(\App\Services\Crm\Crm::class);

// Per-center token override
$crm = $crm->withToken($site->crm_token);
```

---

## Resources Overview

| Resource | Class | What it gives you |
|---|---|---|
| `$crm->students()` | `Students` | Student list, search LOV, session presence |
| `$crm->registrations()` | `Registrations` | All enrollments |
| `$crm->payments()` | `Payments` | Payments, checks, allocations, receivables |
| `$crm->groups()` | `Groups` | Level-sessions and classes (bulk + paginated) |
| `$crm->lov()` | `Lov` | All lookup/reference tables |
| `$crm->subscriptionServices()` | `SubscriptionServices` | Per-student services / installments |
| `$crm->employeeSalaries()` | `EmployeeSalaries` | Teacher salary-per-class records |
| `$crm->attendance()` | `Attendance` | Bulk attendance, students, registrations |

---

## 1. Students

### `students()->list()`
**Endpoint:** `GET /api/external/v1/students`  
**Scope:** `students:read`

```php
$crm->students()->list(
    page: 0,
    size: 50,
    includeTotal: true,
    strStoreId: 12,          // filter by center
    firstName: 'Yassine',
    lastName: 'Benmoussa',
    phoneNumber: '0612...',
    sexe: 'M',
    categoryId: 3,
    registrationStatus: 'ACTIVE',
    reference: 'STU-001',
);
```

**Response rows include:** `STUDENT_ID`, `FIRST_NAME`, `LAST_NAME`, `PHONE_NUMBER`, `WHATSAPP_NUMBER`, `EMAIL`, `CNE`/`IDENTITY_ID`, `STR_STORE_ID`, `CATEGORY_ID`, etc.

---

### `students()->search()`
**Endpoint:** `GET /api/external/v1/lov/students`  
Paginated LOV search. Same page/size params. Used for autocomplete dropdowns.

---

### `students()->sessionPresence()`
**Endpoint:** `GET /api/external/v1/session-presence`  
**Scope:** `session-presence:read`

```php
$crm->students()->sessionPresence(
    page: 0,
    size: 100,
    strStoreId: 12,
    date: '2026-06-01',       // single day ISO format
    classId: 445,
    studentId: 789,
    presence: 'Y',            // filter only present
    absence: 'Y',             // filter only absent
    presenceStatus: 1,        // 1=present, 0=absent
    schoolYearId: 5,
    levelSessionId: 22,
    sessionId: 33,
    registrationId: 44,
);
```

**Response rows include:** `STUDENT_ID`, `FIRST_NAME`, `LAST_NAME`, `SESSION_ID`, `SESSION_DATE` (UTC â€” convert to `Africa/Casablanca`!), `PRESENCE` ("Y"/"N"), `ABSENCE` ("Y"/"N"), `PRESENCE_STATUS` (1/0)

> **Timezone gotcha:** `SESSION_DATE` is emitted in UTC. Evening Casablanca sessions land on the previous calendar day if you don't call `setTimezone('Africa/Casablanca')`.

---

## 2. Registrations

### `registrations()->list()`
**Endpoint:** `GET /api/external/v1/registrations`  
**Scope:** `registrations:read`

```php
$crm->registrations()->list(
    page: 0,
    size: 100,
    strStoreId: 12,
    schoolYearId: 5,
    studentId: 789,
    registrationStatusId: 8,       // 8=active, 9=suspended, 10=cancelled, 11=archived
    levelSessionId: 22,
    levelSessionPackageIds: '10,11,12',
    startDate: '2026-01-01',
    endDate: '2026-12-31',
    filterStatus: 'open',
    reference: 'REG-001',
);
```

**Response rows include:** `REGISTRATION_ID`, `STUDENT_ID`, `START_DATE`, `END_DATE`, `REGISTRATION_STATUS_ID`, `LEVEL_SESSION_ID`, `STR_STORE_ID`, etc.

---

## 3. Payments

### `payments()->list()`
**Endpoint:** `GET /api/external/v1/payments`  
**Scope:** `payments:read`

```php
$crm->payments()->list(
    strStoreId: 12,
    schoolYearId: 5,
    studentId: 789,
    paymentTypeId: 1,
    paymentStatusId: 2,
    paymentMethodeId: 1,    // NOTE: API keeps typo "Methode"
    startDate: '2026-01-01',
    endDate: '2026-01-31',
);
```

**Response rows include:** `PAYMENT_ID`, `REFERENCE`, `AMOUNT`, `EFFECTIVE_DATE`, `PAYMENT_METHOD_ID`, `PAYMENT_TYPE_ID`, `STUDENT_ID`, `USER_CREATION_FULL_NAME`, `DATE_CREATION`, `DATE_UPDATE`, etc.

---

### `payments()->checks()`
**Endpoint:** `GET /api/external/v1/payment-checks`  
**Scope:** `payment-checks:read`

```php
$crm->payments()->checks(
    strStoreId: 12,
    checkNumber: 'CHK-123',
    studentId: 789,
    paymentCheckStatusId: 1,
    bankId: 3,
    dueDateMin: '2026-01-01',
    dueDateMax: '2026-03-31',
);
```

---

### `payments()->allocations()`
**Endpoint:** `GET /api/external/v1/payment-allocations`  
**Scope:** `payments:read`

Links payments to registrations/services.

```php
$crm->payments()->allocations(
    classId: 445,
    levelSessionId: 22,
    registrationId: 55,
    studentId: 789,
    startDate: '2026-01-01',   // must pair with endDate
    endDate: '2026-01-31',
    effectiveDatePaymentAllocation: '2026-01-15',
    cashBoxId: 2,
);
```

**Response rows include:** `ID`, `AMOUNT`, `PAYMENT_ID`, `PAYMENT_REFERENCE`, `STUDENT_ID`, `STUDENT_FULL_NAME`, `REGISTRATION_ID`, `SERVICE_TYPE_NAME`, `EFFECTIVE_DATE_PAYMENT`, `EFFECTIVE_DATE_PAYMENT_ALLOCATION`

---

### `payments()->collection()`
**Endpoint:** `GET /api/external/v1/payment-collection`  
**Scope:** `payments:read`  
**Size cap:** 25 per page (API limit)

Open receivables / amounts still owed.

```php
$crm->payments()->collection(
    strStoreId: 12,
    schoolYearId: 5,
    studentId: 789,
    registrationId: 55,
    classId: 445,
    levelSessionId: 22,
    levelSessionPackageId: 10,
    levelSessionPackageIds: '10,11',
    serviceTypeIds: '1,2,3',
    dueDateStartDate: '2026-01-01',
    dueDateEndDate: '2026-06-30',
    startDay: 0,      // min payment delay days
    endDay: 30,       // max payment delay days
    subscriptionServiceId: 7,
    prestationId: 8,
    prestationTypeId: 2,
    orderId: 99,
    registrationStatusId: 8,
);
```

**Response rows include:** `REST_AMOUNT` / `OPEN_AMOUNT` / `REMAINING_AMOUNT`, `STUDENT_ID`, `REGISTRATION_ID`, `DUE_DATE`, etc.

---

## 4. Groups

### `groups()->classes()`
**Endpoint:** `GET /api/external/v1/groups/classes`  
**Scope:** `groups:read`

```php
$crm->groups()->classes(
    strStoreId: 12,
    schoolYearId: 5,
    schoolDepartmentId: 1,
    schoolStageId: 2,
    schoolLevelId: 3,
    employeeTeacherId: 42,
    statusId: 1,
    history: 'N',     // 'Y' = include archived groups
);
```

**Response rows include:** `ID`, `CLASS_ID`, `REFERENCE`, `NAME`, `NAME_AR`, `ACTIVE`, `START_DATE`, `END_DATE`, `SCHOOL_LEVEL_NAME`, `EMPLOYEE_TEACHER_FULL_NAME`, `CLASSIFICATION_NAME`, `STATUS_NAME`, `LIST_STUDENT` (JSON), `CLASS_COUNT_STUDENTS_ACTIVE`, `SERVICE_LIST` (JSON), `SCHOOL_YEAR_ID`, `STR_STORE_ID`

---

### `groups()->levelSessions()`
**Endpoint:** `GET /api/external/v1/groups/level-sessions`  

```php
$crm->groups()->levelSessions(strStoreId: 12, schoolYearId: 5);
```

---

### `groups()->bulkClasses()` / `groups()->bulkLevelSessions()`
**Endpoints:** `GET /api/external/v1/bulk/groups/classes` and `.../level-sessions`  
Larger default page size (100). Use for full syncs.

```php
$crm->groups()->bulkClasses(size: 100, strStoreId: 12);
```

---

## 5. LOV (List of Values / Reference Tables)

**Scope:** `lov:read`  
No pagination â€” server enforces a `limit` cap (default 100). Cache these aggressively.

| Method | Endpoint | Returns |
|---|---|---|
| `lov()->schoolLevels()` | `/lov/school-levels` | Language levels (A1, B2, ...) |
| `lov()->registrationStatuses()` | `/lov/registration-statuses` | Active/Suspended/Cancelled/Archived |
| `lov()->registrationConventions()` | `/lov/registration-conventions` | Convention types |
| `lov()->registrationChangeStatusReasons()` | `/lov/registration-change-status-reasons` | Why status changed |
| `lov()->paymentTypes()` | `/lov/payment-types` | Tuition / inscription / etc. |
| `lov()->paymentStatuses()` | `/lov/payment-statuses` | Paid / pending / overdue |
| `lov()->paymentMethods()` | `/lov/payment-methods` | Cash / cheque / transfer |
| `lov()->paymentCheckStatuses()` | `/lov/payment-check-statuses` | Check lifecycle |
| `lov()->levelSessionPackages()` | `/lov/level-session-packages` | Course packages |
| `lov()->categories()` | `/lov/categories` | Student categories |
| `lov()->banks()` | `/lov/banks` | Bank list |
| `lov()->subscriptionServices()` | `/subscription-services` | (paginated, sizeâ‰¤25) |

---

## 6. Subscription Services

### `subscriptionServices()->list()`
**Endpoint:** `GET /api/external/v1/subscription-services`  
**Scope:** `subscription-services:read`  
**Size cap:** 25 per page

Per-student installment plans / what they owe per service.

```php
$crm->subscriptionServices()->list(
    strStoreId: 12,
    schoolYearId: 5,
    studentId: 789,
    registrationId: 55,
    levelSessionId: 22,
    levelSessionPackageId: 10,
    dueDate: '2026-06-30',        // returns services with due_date â‰¤ this
    subscriptionServiceStatusId: 1,
);
```

**Response rows include:** `ID`, `REFERENCE`, `STUDENT_ID`, `REGISTRATION_ID`, `TOTAL_PRICE`, `REST_AMOUNT`, `DUE_DATE`, `SERVICE_TYPE_NAME`

---

## 7. Employee Salaries

### `employeeSalaries()->calculatedSalaryClasses()`
**Endpoint:** `GET /api/external/v1/employee-calculated-salary-classes`  
**Scope:** `employee-salaries:read`  
**Size cap:** 25 per page

CRM-computed salary per teacher per class per month.

```php
$crm->employeeSalaries()->calculatedSalaryClasses(
    strStoreId: 12,
    employeeId: 42,
    classId: 445,
    operationMonth: 6,    // 1..12
    operationYear: 2026,
);
```

**Response rows include:** `PK`, `EMPLOYEE_ID`, `FIRST_NAME`, `LAST_NAME`, `CLASS_ID`, `OPERATION_YEAR`, `OPERATION_MONTH`, `MONTHLY_SALARY`, `REMAINING_AMOUNT`

---

## 8. Attendance (Bulk)

For high-volume syncs. Default page size is 100.

### `attendance()->sessionPresence()`
**Endpoint:** `GET /api/external/v1/bulk/session-presence`

```php
$crm->attendance()->sessionPresence(
    dateFrom: '2026-06-01',
    dateTo: '2026-06-30',
    classId: 445,
    strStoreId: 12,
);
```

### `attendance()->students()`
**Endpoint:** `GET /api/external/v1/bulk/students`

### `attendance()->registrations()`
**Endpoint:** `GET /api/external/v1/bulk/registrations`

```php
$crm->attendance()->registrations(classId: 445, strStoreId: 12);
```

---

## HTTP Client Features

### Parallel Paged Scan
Fetches all pages of a list endpoint in parallel batches (2 concurrent, 300ms inter-batch delay). Falls back to sequential if API doesn't return `totalPages`.

```php
$allRows = $crm->client()->pagedScan(
    path: '/api/external/v1/registrations',
    baseQuery: ['strStoreId' => 12, 'schoolYearId' => 5],
    pageSize: 25,
    maxPages: 80,
    concurrency: 2,
);
```

### Parallel Multi-Query Fetch
One request per query variant, fanned out in parallel. Use when the API only accepts a single filter value but you need many (e.g. attendance per-date).

```php
$allRows = $crm->client()->parallelFetch(
    path: '/api/external/v1/session-presence',
    baseQuery: ['strStoreId' => 12, 'classId' => 445],
    variantQueries: [
        ['date' => '2026-06-01'],
        ['date' => '2026-06-02'],
        ['date' => '2026-06-03'],
    ],
    concurrency: 10,
);
```

### Rate Limit Handling
- 429 â†’ waits `Retry-After` seconds (capped at 30s), retries once
- If still 429 â†’ sets a 60s cool-down flag; subsequent calls fail-fast
- Error class: `App\Services\Crm\CrmException`

---

## Stats & Analytics Layer

These services sit on top of the raw API and provide dashboard-ready data.

### `CrmStatsService` â€” KPI Snapshots
```php
app(\App\Services\Crm\Stats\CrmStatsService::class)
    ->perCenterKpis(startDate: '2026-01-01', endDate: '2026-06-30');
// Returns: [site_id => ['site', 'students', 'registrations', 'payments', 'classes']]

->paymentsTrend(months: 6);
// Returns: ['labels' => [...], 'series' => [site_id => ['counts' => [...]]]]

->annualSummary(year: 2026, strStoreId: 12);
// Returns: ['labels', 'collecte', 'reste_a_payer', 'encaissments', 'chiffre_affaire'(placeholder), 'depenses'(placeholder)]
```
**Cache:** 5 minutes. All requests fan out via `Http::pool()`.

---

### `InsightsService` â€” Business Analytics
```php
app(\App\Services\Crm\Stats\InsightsService::class)

->cashHandlers(strStoreId: 12, weekStart: Carbon::now()->startOfWeek());
// Per-employee payment breakdown: cash/cheque/transfer counts, outlier detection, hourly chart

->reconciliation(days: 14);
// Day-by-day totals per center with Z-score anomaly flagging

->retention(monthsBack: 8, strStoreId: 12);
// Cohort analysis: active/archived/cancelled/suspended per start month + dropout %

->forecast(months: 6, strStoreId: 12);
// Revenue projection: last-3-months average as baseline per center
```
**Cache:** 15 minutes. Reads from both API and `crm_payment_snapshots` local table.

---

### `PaymentActivityService` â€” Snapshot Diff Engine
Compares daily snapshots to detect changes. **No API calls â€” reads local DB only.**

```php
app(\App\Services\Crm\Stats\PaymentActivityService::class)

->dailyDiff(date: '2026-06-02', strStoreId: 12);
// Returns: deleted / created / amount_changed / late_edits / user_changed â€” all paginated

->paymentHistory(paymentId: 12345);
// Full audit trail: every snapshot of one payment with field-level diff
```

---

### `DuplicateFinder` â€” Student Dedup
```php
app(\App\Services\Crm\Stats\DuplicateFinder::class)->find(strStoreId: 12);
// Scans up to 3000 students, buckets by: phone, WhatsApp, email, CIN, name+center
// Returns: ['scanned', 'groups' => ['phone'=>[], 'email'=>[], 'cin'=>[], 'name'=>[]], 'summary']
```
**Cache:** 5 minutes.

---

## CenterContext â€” Multi-Center Session

Manages which center the admin is currently viewing.

```php
$ctx = app(\App\Services\Crm\CenterContext::class);

$ctx->available();                  // Collection<Site> with crm_store_id set
$ctx->currentStoreId($urlOverride); // int|null (null = all centers)
$ctx->currentToken($urlOverride);   // Bearer token for selected center
$ctx->setStoreId(12);               // Save to session
$ctx->nameForStoreId(12);           // "GLS Casablanca"
```

---

## What You Can Build

### Already Built
- **CRM KPI Dashboard** â€” per-center student/registration/payment/class counts
- **Attendance Import** â€” sync session presence from bulk endpoint â†’ local DB
- **Payment Snapshot** â€” nightly sync of payments to `crm_payment_snapshots`
- **Payment Activity Feed** â€” daily diff: new/deleted/changed payments
- **Duplicate Student Finder** â€” phone/email/CIN/name matching
- **Retention Funnel** â€” cohort dropout analysis
- **Revenue Forecast** â€” moving-average projection

### What the API Supports That's Not Yet Built

| Feature | Endpoints Needed | Effort |
|---|---|---|
| **Receivables Dashboard** (who owes what) | `payment-collection`, `subscription-services` | Low â€” data already mapped |
| **Check Tracking** (post-dated cheques calendar) | `payment-checks` + `lov/banks` | Low |
| **Teacher Salary Report** | `employee-calculated-salary-classes` | Low â€” endpoint exists |
| **Payment Allocation Audit** (which payment covers which service) | `payment-allocations` | Low |
| **Student Profile Page** | `students`, `registrations`, `payments`, `session-presence` | Medium |
| **Group Enrollment Report** | `groups/classes` (LIST_STUDENT JSON) + `registrations` | Medium |
| **Per-Package Revenue** | `payment-collection` filtered by `levelSessionPackageIds` | Low |
| **School Level / Department Drill-Down** | `lov/school-levels`, `groups/level-sessions` | Medium |
| **Registration Status Change Log** | `lov/registration-change-status-reasons` + `registrations` | Medium |
| **Annual Fee Statement per Student** | `payments` + `subscription-services` + PDF via DomPdf | Medium |
| **Late Payment Alerts** | `payment-collection` with `dueDateEndDate=today` | Low |
| **Center Comparison Charts** | `perCenterKpis()` already returns data â€” just add UI | Low |
| **Teacher Workload vs Salary** | `groups/classes` (CLASS_COUNT_STUDENTS_ACTIVE) + `employee-calculated-salary-classes` | Medium |

---

## Config Reference

```env
CRM_API_BASE_URL=https://your-crm.example.com
CRM_API_TOKEN=your-global-bearer-token
CRM_API_CACHE_TTL=60          # seconds, 0 = disabled
CRM_API_TIMEOUT=15
CRM_API_CONNECT_TIMEOUT=5
CRM_API_VERIFY_SSL=true
```

`config/crm.php` maps these into the `WimschoolClient` constructor. Per-center tokens are stored in `sites.crm_token` and injected via `Crm::withToken()`.
