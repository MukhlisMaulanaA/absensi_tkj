# SweetAlert2 Integration Guide

This project now includes SweetAlert2 for custom alert notifications. SweetAlert provides beautiful, responsive alerts and confirmations.

## Installation

SweetAlert2 is already installed via npm: `sweetalert2`

## Setup

The SweetAlert2 library is automatically initialized in `resources/js/sweetalert.js` and imported in `resources/js/app.js`.

## Usage in JavaScript/Alpine.js

### Global Access
SweetAlert is available globally as `window.Swal`:

```javascript
// Simple alert
Swal.fire('Hello!', 'This is a message', 'info')

// Confirmation dialog
Swal.fire({
  title: 'Are you sure?',
  text: "This action cannot be undone!",
  icon: 'warning',
  showCancelButton: true,
  confirmButtonText: 'Yes, delete!',
  cancelButtonText: 'Cancel',
}).then((result) => {
  if (result.isConfirmed) {
    // Handle confirmation
  }
})
```

### Helper Functions
The following helper functions are available:

```javascript
import { confirmAction, showSuccess, showError, showInfo } from 'resources/js/sweetalert.js'

// Show a confirmation dialog
confirmAction('Confirm Action', 'Are you sure you want to proceed?', 'warning')
  .then((result) => {
    if (result.isConfirmed) {
      // Handle confirmation
    }
  })

// Show success alert
showSuccess('Success!', 'Action completed successfully')

// Show error alert
showError('Error!', 'Something went wrong')

// Show info alert
showInfo('Info', 'Here is some information')
```

## Usage in Filament Actions

### Option 1: Keep Filament Built-in Notifications (Recommended)
Filament's built-in `notify()` method is optimized for the admin panel and should be used for standard notifications:

```php
protected function getHeaderActions(): array
{
    return [
        Action::make('approve')
            ->action(function () {
                // Your logic here
                $this->notify('success', 'Action completed!');
            }),
    ];
}
```

### Option 2: Use SweetAlert via JavaScript Dispatch
For custom SweetAlert notifications in Filament actions, use JavaScript dispatch:

```php
use Filament\Notifications\Notification;

protected function getHeaderActions(): array
{
    return [
        Action::make('customAction')
            ->action(function () {
                // Your logic here
                
                // Dispatch JavaScript to show SweetAlert
                $this->dispatch('showSweetAlert', [
                    'title' => 'Success!',
                    'message' => 'Action completed!',
                    'icon' => 'success'
                ]);
            }),
    ];
}
```

Then in your view or JavaScript, listen for the event:

```javascript
document.addEventListener('livewire:navigated', () => {
    window.addEventListener('showSweetAlert', (event) => {
        Swal.fire(event.detail)
    })
})
```

## Styling

The default SweetAlert styles have been configured to match Filament's design system. You can customize the appearance by modifying `resources/js/sweetalert.js`.

## When to Use SweetAlert vs Filament Notifications

- **Use Filament's `notify()`** for standard success/error/info messages in admin actions
- **Use SweetAlert** for:
  - Custom confirmation dialogs with specific messaging
  - Complex user interactions
  - Stylized alerts outside the admin panel
  - Multi-step confirmations

## Best Practices

1. Keep Filament's built-in notifications for consistency
2. Use SweetAlert for frontend-specific interactions
3. Always provide clear messaging to users
4. Test on mobile devices as SweetAlert is responsive
