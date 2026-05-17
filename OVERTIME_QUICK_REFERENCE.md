# Quick Reference: Overtime Calculation Thresholds

## Company Regulations

```
Day 1 (Current Day)
|
16:00 ─────────────────── 23:59 = 1 DAY OVERTIME
|
Day 2 (Following Day)
|
00:00 ─────────────────── 01:59 = 2 DAYS OVERTIME (reached early morning)
      ─────────────────── 05:59 = 3 DAYS OVERTIME (reached morning)
      ─────────────────── 23:59 = 3+ DAYS OVERTIME (reached full day)
```

## Examples in Timeline Format

### Example 1: Same Day Overtime
```
Start: 16:00 (4 PM)
End:   20:00 (8 PM)
─────────────────────────
Result: 1 DAY ✓
```

### Example 2: Early Morning Overtime
```
Start: 16:00 (4 PM) - Day 1
End:   02:00 (2 AM) - Day 2
─────────────────────────
Result: 2 DAYS ✓
(Extends into early morning of Day 2)
```

### Example 3: Morning Overtime
```
Start: 16:00 (4 PM) - Day 1
End:   06:00 (6 AM) - Day 2
─────────────────────────
Result: 3 DAYS ✓
(Extends into morning of Day 2)
```

### Example 4: Afternoon Overtime
```
Start: 16:00 (4 PM) - Day 1
End:   14:00 (2 PM) - Day 2
─────────────────────────
Result: 3 DAYS ✓
(Extends full afternoon of Day 2)
```

## Implementation Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    OVERTIME REQUEST FLOW                     │
└─────────────────────────────────────────────────────────────┘

USER CREATES REQUEST
        ↓
   [Blade Template]
   resources/views/overtime/create.blade.php
   - Time picker (16:00 - XX:XX)
   - Real-time calculation display
   - JavaScript: calculateOvertimeDays()
        ↓
   SUBMIT FORM
        ↓
   [OvertimeRequestController@store]
   app/Http/Controllers/OvertimeRequestController.php
   - Validate inputs
   - Call OvertimeCalculationService
   - Store with overtime_days
        ↓
   [OvertimeCalculationService]
   app/Services/OvertimeCalculationService.php
   - calculateOvertimeDays()
   - getOvertimeDayDescription()
   - getOvertimeCalculationDetails()
        ↓
   [OvertimeRequest Model]
   app/Models/OvertimeRequest.php
   - Save to database
   - Calculate if needed
   - Format for display
        ↓
   DATABASE
   overtime_requests table + overtime_days column
        ↓
   ADMIN VIEWS RESULT
        ↓
   [Filament Admin Panel]
   - OvertimeRequestResource
   - OvertimeRequestsTable (list view)
   - OvertimeRequestInfolist (detail view)
   - Display with badges and formatting

```

## Data Flow Diagram

```
INPUT TIMES (16:00 - 06:00)
            ↓
     OvertimeCalculationService
     calculateOvertimeDays()
            ↓
    Check end time hour:
    - 00:00-01:59 → 2 days
    - 02:00-05:59 → 3 days
    - 06:00+ → 3 days
            ↓
    Return: Integer (1-3+)
            ↓
    Store in Database
    + Display in UI
```

## Component Breakdown

### 1. Frontend (Blade + JavaScript)
```
create.blade.php
├── Time Picker (16:00 to 23:59)
├── Calculation Result Table
│   ├── Start Time
│   ├── End Time  
│   ├── Duration
│   └── ⭐ Overtime Days (colored badge)
└── Submit Button
    └── Sends: overtime_days value
```

### 2. Backend (Controller + Service)
```
OvertimeRequestController
├── store()
│   ├── Validate input
│   ├── Call OvertimeCalculationService
│   ├── Calculate overtime_days
│   └── Save to database
│
OvertimeCalculationService
├── calculateOvertimeDays()
├── getOvertimeDayDescription()
└── getOvertimeCalculationDetails()
```

### 3. Admin Panel (Filament)
```
OvertimeRequestResource
├── Form (read-only overtime_days)
├── Table (sortable overtime_days column)
├── Infolist (prominent display)
└── Actions (approve/reject)
```

## Key Files at a Glance

| File | Purpose | Key Method |
|------|---------|-----------|
| `OvertimeCalculationService.php` | Core calculation logic | `calculateOvertimeDays()` |
| `OvertimeRequest.php` | Model with helpers | `calculateOvertimeDays()`, `getOvertimeDaysLabel()` |
| `OvertimeRequestController.php` | Request handling | `store()` |
| `create.blade.php` | User form + real-time calc | `calculateOvertimeDays()` (JS) |
| `OvertimeRequestsTable.php` | Admin list view | Column config |
| `OvertimeRequestInfolist.php` | Admin detail view | Badge display |

---
Generated: May 17, 2026
