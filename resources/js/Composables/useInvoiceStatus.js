
import { ref, computed } from 'vue';

export default function useInvoiceStatus(pembelian, pembayaran) {
  const getStageNumber = computed(() => {
    if (!pembelian || !pembayaran) return 1;
    
    if (pembelian.status === 'completed') return 4;
    if (pembelian.status === 'processing') return 3;
    if (pembayaran.status === 'paid') return 3;
    if (pembayaran && pembayaran.payment_link) return 2;
    
    return 1;
  });

  const stageLabels = {
    1: 'Transaksi Dibuat',
    2: 'Pembayaran',
    3: 'Sedang Di Proses',
    4: 'Transaksi Selesai'
  };

  const stageDescriptions = {
    1: 'Transaksi telah berhasil dibuat',
    2: 'Silakan melakukan pembayaran',
    3: 'Pembelian sedang dalam proses',
    4: 'Transaksi telah berhasil diselesaikan'
  };

  const getStatusLabel = (status) => {
    const statusMap = {
      'pending': 'PENDING',
      'paid': 'PAID',
      'failed': 'FAILED',
      'cancelled': 'CANCELLED',
      'processing': 'PROCESSING',
      'completed': 'COMPLETED',
    };
    
    return statusMap[status] || status.toUpperCase();
  };

  const getStatusClass = (status) => {
    const statusClassMap = {
      'pending': 'bg-yellow-500',
      'paid': 'bg-green-500',
      'failed': 'bg-red-500',
      'cancelled': 'bg-gray-500',
      'processing': 'bg-blue-500',
      'completed': 'bg-green-500',
    };
    
    return statusClassMap[status] || 'bg-gray-500';
  };

  return {
    getStageNumber,
    stageLabels,
    stageDescriptions,
    getStatusLabel,
    getStatusClass,
  };
}
