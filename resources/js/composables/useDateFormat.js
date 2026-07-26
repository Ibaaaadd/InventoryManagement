export function useDateFormat() {
  const formatDate = (dateString) => {
    if (!dateString) return '-';
    
    try {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      }).format(date);
    } catch (error) {
      console.error('Error formatting date:', error);
      return dateString;
    }
  };

  const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    
    try {
      const date = new Date(dateString);
      const formattedDate = new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      }).format(date);
      
      const formattedTime = new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
      }).format(date);
      
      return `${formattedDate}, ${formattedTime}`;
    } catch (error) {
      console.error('Error formatting datetime:', error);
      return dateString;
    }
  };

  const formatRelative = (dateString) => {
    if (!dateString) return '-';
    
    try {
      const date = new Date(dateString);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      
      if (diffInSeconds < 60) {
        return 'Baru saja';
      }
      
      const diffInMinutes = Math.floor(diffInSeconds / 60);
      if (diffInMinutes < 60) {
        return `${diffInMinutes} menit yang lalu`;
      }
      
      const diffInHours = Math.floor(diffInMinutes / 60);
      if (diffInHours < 24) {
        return `${diffInHours} jam yang lalu`;
      }
      
      const diffInDays = Math.floor(diffInHours / 24);
      if (diffInDays < 7) {
        return `${diffInDays} hari yang lalu`;
      }
      
      if (diffInDays < 30) {
        const weeks = Math.floor(diffInDays / 7);
        return `${weeks} minggu yang lalu`;
      }
      
      if (diffInDays < 365) {
        const months = Math.floor(diffInDays / 30);
        return `${months} bulan yang lalu`;
      }
      
      const years = Math.floor(diffInDays / 365);
      return `${years} tahun yang lalu`;
    } catch (error) {
      console.error('Error formatting relative time:', error);
      return dateString;
    }
  };

  return {
    formatDate,
    formatDateTime,
    formatRelative,
  };
}
