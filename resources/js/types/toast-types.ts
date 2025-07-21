// resources/js/types/ToastTypes.ts

/**
 * Defines the structure for a toast notification.
 */
export interface ToastNotificationData {
  type: 'success' | 'error' | 'info' | 'warning'; // Enforces specific toast types
  title?: string; // Optional title for the toast
  message: string; // The main message content
  duration?: number; // Optional duration in milliseconds (overrides default)
}