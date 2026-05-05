# Attendance Module - Admin Guide

## Overview

The attendance module has been improved with better layout organization and Google Maps integration to help administrators easily view and analyze employee attendance records.

## Features

### 1. **Improved Table View**
The attendance data table now displays information in an easy-to-read format:

- **Employee & Location**: Shows employee name and assigned location
- **Status Badge**: Quick visual indicator of attendance status
  - 🟢 **On Time** (Green): Employee checked in on time
  - 🟡 **Late** (Yellow): Employee checked in late
  - 🔴 **Absent** (Red): No check-in recorded
- **Check-In & Check-Out Times**: Formatted dates with times for easy reading
- **Working Hours**: Automatically calculated total hours worked
- **Late Minutes**: Number of minutes employee was late
- **Within Radius**: Visual icon showing if check-in/out was within allowed geofence
- **Default Sorting**: Records sorted by most recent check-in first

### 2. **Enhanced Detail View**
When viewing a single attendance record, information is organized into collapsible sections:

#### Employee Information Section
- Employee name (clickable link to employee profile)
- Email address
- Assigned location

#### Attendance Status Section
- Status badge (On Time, Late, Absent)
- Late minutes count
- Geofence radius check

#### Check-In Details Section
- Check-in time with date
- GPS coordinates (latitude & longitude)
- Check-in photo (if available)

#### Check-Out Details Section
- Check-out time with date
- GPS coordinates
- Check-out photo (if available)
- Total working hours

#### Location Map Section
- **Interactive Google Map** showing:
  - 🟢 Green marker for check-in location
  - 🔴 Red marker for check-out location
  - Line connecting the two locations (if both exist)
  - Info windows with timestamps and coordinates
  - Zoom and pan controls
  - Map type selection

#### Record Information Section
- Creation date/time
- Last update date/time
- Deletion date/time (if soft-deleted)

### 3. **Improved Form**
The edit form is now organized with sections:

- **Employee & Location**: Select employee and work location
- **Check-In Details**: Enter or edit check-in time and location
- **Check-Out Details**: Enter or edit check-out time and location
- **Attendance Status**: Set late minutes and geofence radius validation

## Google Maps Configuration

### Prerequisites
To display Google Maps in the attendance detail view, you need a Google Maps API key.

### Setup Steps

1. **Get a Google Maps API Key**:
   - Go to [Google Cloud Console](https://cloud.google.com/maps-platform)
   - Create a new project
   - Enable the "Maps JavaScript API"
   - Create an API key
   - (Optional but recommended) Restrict the key to your domain

2. **Add API Key to Environment**:
   - Open your `.env` file
   - Add the following line:
     ```
     GOOGLE_MAPS_API_KEY=your_api_key_here
     ```
   - Replace `your_api_key_here` with your actual API key

3. **Verify Configuration**:
   - Go to any attendance record's detail view
   - If the map appears, configuration is successful
   - If you see a warning message, check that the API key is correct in `.env`

### Map Features

- **Multiple Markers**: Shows both check-in and check-out locations
- **Connection Line**: A line is drawn between check-in and check-out points
- **Info Windows**: Click on markers to see detailed location information
- **Zoom Controls**: Adjust zoom level to see more detail
- **Map Type Selection**: Switch between Map, Satellite, and Terrain views
- **Fullscreen**: Expand the map to fullscreen view

## Tips for Admins

1. **Quick Status Check**: Use the Status column to quickly identify attendance issues
2. **Geofence Verification**: Use the "Within Radius" column to verify employees are checking in at correct locations
3. **Working Hours Tracking**: Monitor the "Working Hours" column to ensure proper timekeeping
4. **Location Verification**: Use the map view to verify check-in/out locations are accurate
5. **Filtering**: Use search to find specific employees or dates
6. **Bulk Actions**: Select multiple records to delete or restore (soft-deleted records)

## Troubleshooting

### Map Not Displaying
- **Issue**: Blank space where map should be
- **Solution**: 
  - Verify `GOOGLE_MAPS_API_KEY` is set in `.env`
  - Ensure the API key has Maps JavaScript API enabled
  - Check browser console for any API errors
  - Clear cache and reload page

### Missing Coordinates
- **Issue**: Map shows but no markers appear
- **Solution**: 
  - Verify the attendance record has check-in coordinates
  - Ensure latitude and longitude values are valid numbers

### "API Key not configured" Warning
- **Solution**: Add the `GOOGLE_MAPS_API_KEY` to your `.env` file as described in Setup Steps

## Performance Notes

- Maps are only loaded when viewing individual attendance records
- Multiple maps on the page will load independently
- Maps automatically resize with responsive layout
- Large datasets in table view load efficiently with pagination

## Security Notes

- API key restrictions are recommended (domain restriction)
- Keep API key in `.env` file, never commit to version control
- Monitor API usage in Google Cloud Console to prevent unexpected charges
