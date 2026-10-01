export const API_URL = import.meta.env.VITE_API_URL || '/backend'
export const APP_NAME = 'ShopVault'
export const CURRENCY = '₹'
export const DEFAULT_LIMIT = 20
export const MAX_LIMIT = 100
export const ORDER_STATUS = {
  pending: { label: 'Pending', color: '#f59e0b' },
  processing: { label: 'Processing', color: '#3b82f6' },
  shipped: { label: 'Shipped', color: '#8b5cf6' },
  delivered: { label: 'Delivered', color: '#10b981' },
  cancelled: { label: 'Cancelled', color: '#ef4444' }
}
export const PAYMENT_STATUS = {
  pending: { label: 'Pending', color: '#f59e0b' },
  paid: { label: 'Paid', color: '#10b981' },
  failed: { label: 'Failed', color: '#ef4444' },
  refunded: { label: 'Refunded', color: '#6b7280' }
}
