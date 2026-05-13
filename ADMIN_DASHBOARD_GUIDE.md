# Admin Dashboard - Implementation Guide

## Overview
The Filament Admin Dashboard has been completely optimized to provide admins with comprehensive attendance management insights through interactive widgets, real-time statistics, and chart visualizations.

## Dashboard Layout

The dashboard is organized into 4 key sections:

### 1. **Daily Quick Stats** (EmployeeAttendanceStatsWidget)
- **Total Employees**: Count of all active employees in the system
- **Present Today**: Number and percentage of employees checked in
- **Late Today**: Number and percentage of employees who checked in late
- **Absent Today**: Number and percentage of absent employees

Color coding: Info (blue) for total, Success (green) for present, Warning (orange) for late, Danger (red) for absent.

### 2. **Visual Attendance Analytics** (Doughnut Chart)
- **On-Time vs Late Breakdown**: Pie chart showing today's attendance status distribution
- Quick visual reference for attendance patterns
- Green for on-time, Red for late

### 3. **Attendance Trends** (Line Charts)

#### a. **Daily Attendance Trend (Last 7 Days)**
- Shows number of employees present each day
- Helps identify peak attendance days
- Useful for workload planning

#### b. **Average Working Hours Trend (Last 7 Days)**
- Daily average working hours
- Identifies productivity patterns
- Helps detect overtime trends

#### c. **Geofence Compliance (Last 7 Days)**
- Dual-line chart: Within geofence vs violations
- Blue line: Employees checking in from correct location
- Red line: Employees checking in from outside designated area

### 4. **Detailed Metrics & Lists**

#### Monthly Attendance Summary (StatsWidget)
- **Total Unique Attendees**: Employees who attended this month
- **Average Daily Attendance**: Calculated per working day
- **Geofence Violations**: Total check-ins outside designated area
- **Average Working Hours**: Average daily working hours for the month

#### Recent Overtime Requests (Table)
- Employee name and position
- Overtime reason and requested date
- Status with color badges:
  - **Warning** (Orange): Pending
  - **Success** (Green): Approved
  - **Danger** (Red): Rejected
- Duration in hours

#### Late Employees Today (Table)
- Real-time list of late employees
- Minutes late (color-coded: Red if >30 min, Orange if ≤30 min)
- Employee position and assigned location
- Check-in timestamp
- Geofence status indicator

## Key Features

✅ **Real-Time Data**: All statistics update with current attendance data  
✅ **Responsive Design**: Adapts to mobile (1 col), tablet (2 cols), and desktop (3 cols)  
✅ **Color Coding**: Intuitive visual indicators for quick status recognition  
✅ **Chart Visualizations**: Line and doughnut charts for trend analysis  
✅ **Sortable Tables**: Late employees and overtime requests with easy filtering  
✅ **Performance Metrics**: Geofence compliance and working hours tracking  

## Widget Sort Order

Widgets are displayed in this priority order:

1. Employee Attendance Stats (Daily overview)
2. Attendance Status (Doughnut chart)
3. Daily Attendance Trend (Line chart)
4. Average Working Hours (Line chart)
5. Geofence Violations (Line chart)
6. Monthly Attendance Stats (Monthly overview)
7. Recent Overtime Requests (Table)
8. Late Employees (Table)

## Data Calculations

### Late Calculation
- Based on `late_minutes` field in Attendance model
- Compared against user's scheduled work time
- Threshold: Any late_minutes > 0 is considered late

### Working Hours Calculation
- Difference between check_out_time and check_in_time
- Converted to decimal hours
- Ignores incomplete check-in/check-out records

### Attendance Rate
- Present Today ÷ Total Employees × 100 = Attendance %
- Useful for measuring daily attendance health

### Geofence Compliance
- Percentage of check-ins within designated radius
- Tracked via `is_within_radius` boolean field
- Critical for location-based management

## Customization Tips

### Modify Widget Appearance
Each widget can be customized:

```php
// In AdminDashboard.php
public function getColumns(): int | array
{
    return [
        'default' => 1,  // Mobile
        'md' => 2,       // Tablet
        'lg' => 4,       // Desktop - can increase for more columns
        'xl' => 4,
    ];
}
```

### Change Chart Colors
Edit the widget's `getData()` method:

```php
'borderColor' => '#10b981',           // Change line color
'backgroundColor' => 'rgba(...)',     // Change fill color
```

### Adjust Time Ranges
Modify the loop range in chart widgets:

```php
// Currently: for ($i = 6; $i >= 0; $i--) // Last 7 days
// For 30 days:
for ($i = 29; $i >= 0; $i--)
```

### Add More Widgets
Create a new widget and add to AdminDashboard::getWidgets():

```php
public function getWidgets(): array
{
    return [
        // ... existing widgets
        YourNewWidget::class,
    ];
}
```

## Accessing the Dashboard

- **URL**: `/admin/dashboard`
- **Navigation**: Dashboard link in Filament sidebar
- **Permissions**: Only users with admin role (role='admin')

## Performance Considerations

The dashboard queries multiple attendance records. For better performance:

### Optimize Database Indexes
```sql
CREATE INDEX idx_attendance_check_in ON attendances(check_in_time);
CREATE INDEX idx_attendance_user ON attendances(user_id);
CREATE INDEX idx_attendance_geofence ON attendances(is_within_radius);
CREATE INDEX idx_user_role ON users(role);
```

### Cache Results
Consider caching expensive queries in peak hours:

```php
Cache::remember('daily-stats', 3600, function () {
    // Your query here
});
```

## Troubleshooting

### Widgets Not Showing
1. Ensure AdminDashboard is registered in AdminPanelProvider
2. Check widget files exist in `/app/Filament/Widgets/`
3. Verify `->discoverWidgets()` is called in AdminPanelProvider

### Charts Not Rendering
1. Ensure Filament Charts addon is installed
2. Check browser console for JavaScript errors
3. Verify chart data contains valid numbers

### Slow Dashboard Loading
1. Check attendance table has proper indexes
2. Reduce time range in chart widgets
3. Consider implementing pagination on tables

## Future Enhancements

Potential additions to the dashboard:

- **Export Reports**: CSV/PDF export functionality
- **Custom Date Ranges**: Admin-selectable time periods
- **Department Filters**: View stats by department
- **Email Reports**: Scheduled email summaries
- **Absence Tracking**: Detailed reason for absences
- **Performance Alerts**: Automatic notifications for policy violations
- **Predictive Analytics**: ML-based trend predictions
