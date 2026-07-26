export function useStatusBadge() {
  const getVariant = (status) => {
    const statusLower = String(status).toLowerCase();
    
    const variantMap = {
      pending: 'warning',
      approved: 'success',
      rejected: 'danger',
      active: 'success',
      inactive: 'default',
      in: 'success',
      out: 'warning',
      success: 'success',
      warning: 'warning',
      danger: 'danger',
      info: 'info',
      default: 'default',
    };

    return variantMap[statusLower] || 'default';
  };

  const formatStatus = (status) => {
    if (!status) return '';
    return String(status).charAt(0).toUpperCase() + String(status).slice(1).toLowerCase();
  };

  return {
    getVariant,
    formatStatus,
  };
}
