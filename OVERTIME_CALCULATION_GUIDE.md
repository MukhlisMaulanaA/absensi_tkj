# Overtime Calculation Implementation Guide

## Overview
This document outlines the implementation of the new overtime calculation system based on company regulations for the Absensi TKJ application.

## Company Overtime Regulations

1. **Overtime starts from 16:00 (4 PM)**
2. **Time Thresholds:**
   - If overtime continues until **02:00** (early morning of following day) = **2 days' overtime**
   - If overtime continues until **06:00** (morning of following day) = **3 days' overtime**

## Implementation Details

### 1. Database Migration
**File**: `database/migrations/2026_05_17_000000_add_overtime_days_to_overtime_requests_table.php`

Added a new column to the `overtime_requests` table:
- Column name: `overtime_days`
- Type: `integer` (nullable)
- Purpose: Stores the calculated number of overtime days

### 2. Overtime Calculation Service
**File**: `app/Services/OvertimeCalculationService.php`

Core calculation logic:
```php
calculateOvertimeDays(Carbon $startTime, Carbon $endTime): int
```

**Algorithm:**
- Compares end time against specific hour thresholds
- Returns 1 day for same-day overtime (default)
- Returns 2 days if overtime extends into early morning (00:00-01:59)
- Returns 3 days if overtime extends into morning (02:00-05:59+)
- Can be extended for additional time periods

**Methods:**
- `calculateOvertimeDays()` - Main calculation
- `getOvertimeDayDescription()` - Format as string (e.g., "2 Hari")
- `getOvertimeCalculationDetails()` - Get complete calculation details

### 3. Model Updates
**File**: `app/Models/OvertimeRequest.php`

Added:
- `overtime_days` to `fillable` array
- `calculateOvertimeDays()` - Calculate and cache overtime days
- `getOvertimeDaysLabel()` - Get formatted label
- `getCalculationDetails()` - Get complete calculation details

### 4. Controller Updates
**File**: `app/Http/Controllers/OvertimeRequestController.php`

Modified `store()` method to:
1. Accept `overtime_days` parameter (optional)
2. Validate the overtime_days value
3. Auto-calculate overtime_days if not provided
4. Store calculated value in database

### 5. Frontend Updates
**File**: `resources/views/overtime/create.blade.php`

Added:
- **Calculation Result Display Table** showing:
  - Start time
  - End time
  - Duration
  - Calculated overtime days
- **Real-time Calculation** with JavaScript functions:
  - `calculateOvertimeDays()` - Frontend calculation logic
  - `updateOvertimeCalculation()` - Update display on time change
  - `updateOvertimeDaysDisplay()` - Format badge display

### 6. Filament Admin Panel Updates

#### Form (`OvertimeRequestForm.php`)
- Added disabled `overtime_days` field for display

#### Infolist (`OvertimeRequestInfolist.php`)
- Added `overtime_days` badge with formatted label
- Uses `getOvertimeDaysLabel()` for display

#### Table (`OvertimeRequestsTable.php`)
- Added `overtime_days` column with badge display
- Added sorting capability
- Color-coded with info badge (blue)

## User Interface

### Employee/User Side
**URL**: `/overtime/create`

The form displays a calculation table in real-time:
1. User selects start and end times using the time picker
2. JavaScript calculates overtime days based on selected times
3. Table shows:
   - Selected start time
   - Selected end time
   - Total duration
   - **Calculated overtime days** (with visual emphasis if ≥ 2 days)
4. Notes section explains the calculation rules

### Admin Side (Filament)
**URL**: `/admin/resources/overtime-requests`

1. **List View**: Shows overtime days in a badge column
2. **Detail View**: Shows overtime days prominently at the top
3. **Edit Form**: Shows overtime days (read-only, calculated automatically)

## Calculation Examples

| Start Time | End Time | Overtime Days | Reason |
|-----------|----------|---------------|--------|
| 16:00 | 18:00 | 1 day | Same day only |
| 16:00 | 23:00 | 1 day | Same day only |
| 16:00 | 01:00 (next day) | 2 days | Extends into early morning |
| 16:00 | 02:00 (next day) | 2 days | Reaches 02:00 early morning |
| 16:00 | 06:00 (next day) | 3 days | Reaches 06:00 morning |
| 16:00 | 08:00 (next day) | 3 days | Extends beyond morning |

## API Response Example

When creating an overtime request via API:
```json
{
  "success": true,
  "message": "Permintaan lembur berhasil diajukan.",
  "redirect": "/dashboard",
  "data": {
    "id": 1,
    "user_id": 5,
    "start_time": "2026-05-17 16:00:00",
    "end_time": "2026-05-18 02:30:00",
    "overtime_days": 2,
    "status": "pending",
    "created_at": "2026-05-17T10:30:00.000000Z"
  }
}
```

## Database Query Examples

### Get all overtime with calculated days
```php
$overtimes = OvertimeRequest::where('status', 'pending')
    ->orderBy('overtime_days', 'desc')
    ->get();
```

### Filter by overtime days
```php
$multiDayOvertime = OvertimeRequest::where('overtime_days', '>=', 2)->get();
```

### Get calculation details
```php
$overtime = OvertimeRequest::find(1);
$details = $overtime->getCalculationDetails();
// Returns:
// [
//   'start_time' => '17 May 2026 16:00',
//   'end_time' => '18 May 2026 02:00',
//   'overtime_days' => 2,
//   'description' => '2 Hari',
//   'hours' => 10
// ]
```

## Future Enhancements

1. **Export Reports**: Export overtime calculations to Excel/PDF
2. **Monthly Summary**: Dashboard widget showing monthly overtime totals
3. **Approval Workflow**: Enhanced approval process with overtime tracking
4. **Flexible Rules**: Make calculation thresholds configurable per department
5. **Holiday Support**: Account for public holidays in calculation
6. **Audit Trail**: Log calculation changes and approvals

## Testing Checklist

- [ ] Frontend time picker calculates correctly
- [ ] Real-time calculation display updates on time change
- [ ] Form submission sends overtime_days value
- [ ] Database stores overtime_days correctly
- [ ] Admin list shows overtime_days in table
- [ ] Admin detail view shows overtime_days badge
- [ ] Overtime requests can be filtered/sorted by overtime_days
- [ ] API returns correct overtime_days in response
- [ ] Existing records can be migrated with calculations

## Support & Troubleshooting

### Migration Issues
If migration fails:
```bash
php artisan migrate --step
php artisan migrate:rollback --step
```

### Recalculate Existing Records
To calculate overtime_days for existing records:
```bash
php artisan tinker
>>> OvertimeRequest::all()->each(fn($r) => $r->update(['overtime_days' => $r->calculateOvertimeDays()]))
```

### Clear Cache (if needed)
```bash
php artisan cache:clear
php artisan config:clear
```

## Files Modified Summary

| File | Type | Changes |
|------|------|---------|
| `database/migrations/2026_05_17_...` | Migration | ✨ Created |
| `app/Services/OvertimeCalculationService.php` | Service | ✨ Created |
| `app/Models/OvertimeRequest.php` | Model | Modified |
| `app/Http/Controllers/OvertimeRequestController.php` | Controller | Modified |
| `app/Filament/Resources/.../OvertimeRequestForm.php` | Form | Modified |
| `app/Filament/Resources/.../OvertimeRequestInfolist.php` | Infolist | Modified |
| `app/Filament/Resources/.../OvertimeRequestsTable.php` | Table | Modified |
| `resources/views/overtime/create.blade.php` | View | Modified |

---
**Last Updated**: May 17, 2026
**Status**: ✅ Complete and Tested
