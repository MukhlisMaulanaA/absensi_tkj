import Swal from 'sweetalert2'

// Make SweetAlert available globally
window.Swal = Swal

// Configure default SweetAlert options
Swal.mixin({
  customClass: {
    confirmButton: 'fi-btn fi-btn-lg fi-rounded-lg fi-btn-primary',
    cancelButton: 'fi-btn fi-btn-lg fi-rounded-lg fi-btn-gray',
    denyButton: 'fi-btn fi-btn-lg fi-rounded-lg fi-btn-danger',
  },
  buttonsStyling: false,
})

export default Swal

// Helper function for confirmations with SweetAlert
export const confirmAction = (title = 'Are you sure?', message = '', icon = 'warning') => {
  return Swal.fire({
    title: title,
    text: message,
    icon: icon,
    showCancelButton: true,
    confirmButtonText: 'Yes, proceed!',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
  })
}

// Helper function for success alerts
export const showSuccess = (title = 'Success!', message = '') => {
  return Swal.fire({
    title: title,
    text: message,
    icon: 'success',
    confirmButtonText: 'OK',
  })
}

// Helper function for error alerts
export const showError = (title = 'Error!', message = '') => {
  return Swal.fire({
    title: title,
    text: message,
    icon: 'error',
    confirmButtonText: 'OK',
  })
}

// Helper function for info alerts
export const showInfo = (title = 'Info', message = '') => {
  return Swal.fire({
    title: title,
    text: message,
    icon: 'info',
    confirmButtonText: 'OK',
  })
}
