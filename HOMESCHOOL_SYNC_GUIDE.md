# Homeschool Sync for Professor Payment Calculation

## Overview
This module integrates with the Homeschool external API to automatically sync attendance data and calculate professor payments, replacing manual Excel imports.

## Features
- Direct API integration with Homeschool
- Attendance sync by group and date range
- Full compatibility with existing payment calculation logic
- Sync history logging
- Manual sync via admin panel
- Scheduled automatic sync
- Legacy CRM API import support preserved

## Prerequisites
- `CRM_API_BASE_URL` and `CRM_API_TOKEN` set in `.env`
- Groups must have `crm_class_id` set in the database to map to Homeschool classes

## Usage

### Admin Panel
1. Navigate to: **Backoffice > Paiement Professeurs > CRM Homeschool**
2. Select a group with a valid `crm_class_id`
3. Choose date range
4. Optionally set a custom payment per student (overrides group/teacher default)
5. Click **Synchroniser et calculer**
6. You will be redirected to the import details page showing the calculated payment summary

### Command Line
```bash
# Sync all groups for current month
php artisan homeschool:sync-attendance

# Sync specific groups for custom date range
php artisan homeschool:sync-attendance --group=1 --group=2 --date-start=2024-01-01 --date-end=2024-01-31
```

### Scheduled Sync
The command is scheduled to run automatically on the **1st of every month at 2:00 AM** (see `app/Console/Kernel.php`).

## Files Created/Modified
### New Files
- `app/Services/Crm/Resources/Attendance.php`: Resource class for Homeschool bulk API endpoints
- `app/Services/Payroll/HomeschoolAttendanceService.php`: Core sync & calculation service
- `app/Http/Controllers/Backoffice/Payroll/HomeschoolPayrollController.php`: Admin panel controller
- `app/Models/HomeschoolSyncLog.php`: Sync history model
- `app/Console/Commands/SyncHomeschoolAttendance.php`: Scheduled sync command
- `database/migrations/2026_06_02_102147_create_homeschool_sync_logs_table.php`: Sync logs table migration
- `resources/views/backoffice/payroll/homeschool/index.blade.php`: Admin panel view

### Modified Files
- `app/Services/Crm/Crm.php`: Added `attendance()` method to access new resource
- `app/Models/Group.php`: Added `crm_class_id` to fillable
- `routes/backoffice.php`: Updated CRM prefix routes, added new homeschool sync route
- `app/Console/Kernel.php`: Added scheduled sync command

## Legacy Support
Legacy CRM API imports are still available at `/backoffice/payroll/crm/legacy` for compatibility.

## Payment Logic
The payment calculation logic remains **unchanged** from the original Excel import system:
- Weekly threshold (default 3 days)
- Weekly unit amount = base price × 25% (default)
- Week mapping and 4-week bucket system preserved
- Manual override support for student weekly amounts
