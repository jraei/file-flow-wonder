
<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { BarChart, LineChart, PieChart } from "vue-chartjs";
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement } from 'chart.js';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement);

// Props to receive data from controller
const props = defineProps({
  metrics: Object,
  charts: Object,
  tables: Object,
  period: {
    type: String,
    default: 'day'
  }
});

// State
const isLoading = ref(false);
const selectedPeriod = ref(props.period || 'day');
const startDate = ref('');
const endDate = ref('');
const selectedProduct = ref('');
const productsList = ref([]);
const productServices = ref([]);
const flashsaleEvents = ref([]);
const voucherData = ref([]);

// Computed properties for charts
const revenueChartData = computed(() => {
  if (!props.charts?.revenue_trend) {
    return {
      labels: [],
      datasets: [
        {
          label: 'Revenue',
          data: [],
          borderColor: 'rgba(155, 135, 245, 1)',
          backgroundColor: 'rgba(155, 135, 245, 0.05)',
          fill: true,
        },
        {
          label: 'Profit',
          data: [],
          borderColor: 'rgba(51, 195, 240, 1)',
          backgroundColor: 'rgba(51, 195, 240, 0.05)',
          fill: true,
        }
      ]
    };
  }
  
  return {
    labels: props.charts.revenue_trend.labels,
    datasets: props.charts.revenue_trend.datasets
  };
});

const orderStatusChartData = computed(() => {
  if (!props.charts?.order_stats?.statusDistribution) {
    return {
      labels: [],
      datasets: [
        {
          data: [],
          backgroundColor: [],
          borderWidth: 1,
        }
      ]
    };
  }
  
  return {
    labels: props.charts.order_stats.statusDistribution.labels,
    datasets: props.charts.order_stats.statusDistribution.datasets
  };
});

// Methods
const updatePeriod = async () => {
  if (selectedPeriod.value !== 'custom') {
    isLoading.value = true;
    try {
      const response = await axios.get('/admin/dashboard', {
        params: {
          period: selectedPeriod.value
        }
      });
      // Process and update the data
      Object.assign(props.metrics, response.data.metrics);
      Object.assign(props.charts, response.data.charts);
      Object.assign(props.tables, response.data.tables);
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
    } finally {
      isLoading.value = false;
    }
  }
};

const updateCustomRange = async () => {
  if (!startDate.value || !endDate.value) return;
  
  isLoading.value = true;
  try {
    const response = await axios.get('/admin/dashboard', {
      params: {
        period: 'custom',
        start_date: startDate.value,
        end_date: endDate.value
      }
    });
    // Process and update the data
    Object.assign(props.metrics, response.data.metrics);
    Object.assign(props.charts, response.data.charts);
    Object.assign(props.tables, response.data.tables);
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    isLoading.value = false;
  }
};

const loadProductsList = async () => {
  try {
    const response = await axios.get('/admin/dashboard/products');
    productsList.value = response.data;
  } catch (error) {
    console.error('Error fetching products list:', error);
  }
};

const loadProductServices = async () => {
  if (!selectedProduct.value && selectedProduct.value !== 0) return;
  
  try {
    const response = await axios.get(`/admin/dashboard/product-services/${selectedProduct.value}`, {
      params: { period: selectedPeriod.value }
    });
    productServices.value = response.data.services;
  } catch (error) {
    console.error('Error fetching product services:', error);
  }
};

const loadFlashsaleEvents = async () => {
  try {
    const response = await axios.get('/admin/dashboard/flashsales', {
      params: { period: selectedPeriod.value }
    });
    flashsaleEvents.value = response.data;
  } catch (error) {
    console.error('Error fetching flashsale events:', error);
  }
};

const loadVoucherData = async () => {
  try {
    const response = await axios.get('/admin/dashboard/vouchers', {
      params: { period: selectedPeriod.value }
    });
    voucherData.value = response.data;
  } catch (error) {
    console.error('Error fetching voucher data:', error);
  }
};

// Initialize
onMounted(() => {
  loadProductsList();
  loadFlashsaleEvents();
  loadVoucherData();
});
</script>

<template>
  <AdminLayout>
    <div class="px-4 py-6 space-y-8">
      <!-- Dashboard Header -->
      <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-white">Dashboard Analytics</h1>
        
        <!-- Period Filter -->
        <div class="flex flex-wrap gap-4">
          <div class="flex items-center gap-2">
            <label for="period" class="text-sm font-medium text-white/80">Period:</label>
            <select 
              id="period" 
              v-model="selectedPeriod"
              @change="updatePeriod"
              class="bg-secondary/10 border border-secondary/20 text-white text-sm rounded-lg focus:ring-primary/50 focus:border-primary/50 p-2"
            >
              <option value="day">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="year">This Year</option>
              <option value="lifetime">Lifetime</option>
              <option value="custom">Custom Range</option>
            </select>
          </div>
          
          <!-- Date Range Picker (shown only when custom is selected) -->
          <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-2">
            <input 
              type="date" 
              v-model="startDate" 
              class="bg-secondary/10 border border-secondary/20 text-white text-sm rounded-lg focus:ring-primary/50 focus:border-primary/50 p-2"
            />
            <span class="text-white">to</span>
            <input 
              type="date" 
              v-model="endDate" 
              class="bg-secondary/10 border border-secondary/20 text-white text-sm rounded-lg focus:ring-primary/50 focus:border-primary/50 p-2"
            />
            <button 
              @click="updateCustomRange"
              class="bg-primary/80 hover:bg-primary text-white px-4 py-2 rounded-lg shadow-sm"
            >
              Apply
            </button>
          </div>
        </div>
      </div>

      <!-- Key Metrics Cards -->
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <!-- Users Card -->
        <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-white">Users</h3>
            <div class="p-3 rounded-full bg-primary/20">
              <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
          </div>
          <div class="mb-1 text-3xl font-bold text-white">
            {{ isLoading ? '...' : (metrics?.users?.total?.toLocaleString() || '0') }}
          </div>
          <div class="flex items-center text-sm">
            <span :class="[(metrics?.users?.isPositive ? 'text-green-400' : 'text-red-400')]">
              <span v-if="isLoading">Loading...</span>
              <span v-else>
                {{ metrics?.users?.isPositive ? '↑' : '↓' }} {{ Math.abs(metrics?.users?.growthPercent || 0).toFixed(1) }}%
              </span>
            </span>
            <span class="ml-1 text-white/70">from previous period</span>
          </div>
        </div>

        <!-- Revenue Card -->
        <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-white">Revenue</h3>
            <div class="p-3 rounded-full bg-primary/20">
              <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mb-1 text-3xl font-bold text-white">
            {{ isLoading ? '...' : `Rp ${(metrics?.revenue?.total || 0).toLocaleString()}` }}
          </div>
          <div class="flex items-center text-sm">
            <span :class="[(metrics?.revenue?.isPositive ? 'text-green-400' : 'text-red-400')]">
              <span v-if="isLoading">Loading...</span>
              <span v-else>
                {{ metrics?.revenue?.isPositive ? '↑' : '↓' }} {{ Math.abs(metrics?.revenue?.growthPercent || 0).toFixed(1) }}%
              </span>
            </span>
            <span class="ml-1 text-white/70">from previous period</span>
          </div>
        </div>

        <!-- Orders Card -->
        <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-white">Orders</h3>
            <div class="p-3 rounded-full bg-primary/20">
              <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
          </div>
          <div class="mb-1 text-3xl font-bold text-white">
            {{ isLoading ? '...' : (metrics?.orders?.total?.toLocaleString() || '0') }}
          </div>
          <div class="flex items-center text-sm">
            <span :class="[(metrics?.orders?.isPositive ? 'text-green-400' : 'text-red-400')]">
              <span v-if="isLoading">Loading...</span>
              <span v-else>
                {{ metrics?.orders?.isPositive ? '↑' : '↓' }} {{ Math.abs(metrics?.orders?.growthPercent || 0).toFixed(1) }}%
              </span>
            </span>
            <span class="ml-1 text-white/70">from previous period</span>
          </div>
        </div>

        <!-- Products Card -->
        <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-white">Products</h3>
            <div class="p-3 rounded-full bg-primary/20">
              <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
          <div class="mb-1 text-3xl font-bold text-white">
            {{ isLoading ? '...' : (metrics?.products?.total?.toLocaleString() || '0') }}
          </div>
          <div class="flex items-center text-sm">
            <span :class="[(metrics?.products?.isPositive ? 'text-green-400' : 'text-red-400')]">
              <span v-if="isLoading">Loading...</span>
              <span v-else>
                {{ metrics?.products?.isPositive ? '↑' : '↓' }} {{ Math.abs(metrics?.products?.growthPercent || 0).toFixed(1) }}%
              </span>
            </span>
            <span class="ml-1 text-white/70">from previous period</span>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Revenue Trend Chart (takes 2/3 width on large screens) -->
        <div class="p-6 rounded-lg shadow-lg lg:col-span-2 bg-secondary/10">
          <h3 class="mb-4 text-lg font-medium text-white">Revenue & Profit Trend</h3>
          <div v-if="isLoading" class="flex items-center justify-center h-80">
            <div class="text-secondary">Loading chart data...</div>
          </div>
          <div v-else class="h-80">
            <LineChart 
              v-if="charts?.revenue_trend"
              :data="revenueChartData"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                  y: {
                    beginAtZero: true,
                    grid: {
                      color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                      color: 'rgba(255, 255, 255, 0.7)'
                    }
                  },
                  x: {
                    grid: {
                      color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                      color: 'rgba(255, 255, 255, 0.7)'
                    }
                  }
                },
                plugins: {
                  legend: {
                    labels: {
                      color: 'rgba(255, 255, 255, 0.7)'
                    }
                  }
                }
              }"
            />
          </div>
        </div>

        <!-- Order Status Distribution (takes 1/3 width on large screens) -->
        <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
          <h3 class="mb-4 text-lg font-medium text-white">Order Status</h3>
          <div v-if="isLoading" class="flex items-center justify-center h-80">
            <div class="text-secondary">Loading chart data...</div>
          </div>
          <div v-else class="h-80">
            <PieChart
              v-if="charts?.order_stats?.statusDistribution"
              :data="orderStatusChartData"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    position: 'bottom',
                    labels: {
                      color: 'rgba(255, 255, 255, 0.7)'
                    }
                  }
                }
              }"
            />
          </div>
        </div>
      </div>

      <!-- Top Products Section -->
      <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-white">Top Products</h3>
          <select 
            v-model="selectedProduct"
            @change="loadProductServices"
            class="bg-secondary/20 border border-secondary/30 text-white text-sm rounded-lg focus:ring-primary/50 focus:border-primary/50 p-2"
          >
            <option value="">All Products</option>
            <option v-for="product in productsList" :key="product.id" :value="product.id">
              {{ product.name }}
            </option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-white">
            <thead class="text-xs uppercase bg-secondary/20">
              <tr>
                <th scope="col" class="px-6 py-3">Product</th>
                <th scope="col" class="px-6 py-3">Sales</th>
                <th scope="col" class="px-6 py-3">Revenue</th>
                <th scope="col" class="px-6 py-3">Profit</th>
                <th scope="col" class="px-6 py-3">Growth</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="5" class="px-6 py-4 text-center">Loading top products...</td>
              </tr>
              <tr v-else-if="!tables?.top_products?.length">
                <td colspan="5" class="px-6 py-4 text-center">No products data available</td>
              </tr>
              <tr v-for="product in tables?.top_products" :key="product.id" class="bg-secondary/5 border-b border-white/5 hover:bg-secondary/10">
                <td class="px-6 py-4 font-medium whitespace-nowrap">{{ product.name }}</td>
                <td class="px-6 py-4">{{ product.sales?.toLocaleString() || '0' }}</td>
                <td class="px-6 py-4">Rp {{ product.revenue?.toLocaleString() || '0' }}</td>
                <td class="px-6 py-4">Rp {{ product.profit?.toLocaleString() || '0' }}</td>
                <td class="px-6 py-4">
                  <span :class="[product.growth >= 0 ? 'text-green-400' : 'text-red-400']">
                    {{ product.growth >= 0 ? '↑' : '↓' }} {{ Math.abs(product.growth || 0).toFixed(1) }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Transactions Section -->
      <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
        <h3 class="mb-4 text-lg font-medium text-white">Recent Transactions</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-white">
            <thead class="text-xs uppercase bg-secondary/20">
              <tr>
                <th scope="col" class="px-6 py-3">ID</th>
                <th scope="col" class="px-6 py-3">User</th>
                <th scope="col" class="px-6 py-3">Game</th>
                <th scope="col" class="px-6 py-3">Amount</th>
                <th scope="col" class="px-6 py-3">Profit</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="7" class="px-6 py-4 text-center">Loading transactions...</td>
              </tr>
              <tr v-else-if="!tables?.recent_transactions?.length">
                <td colspan="7" class="px-6 py-4 text-center">No recent transactions</td>
              </tr>
              <tr v-for="tx in tables?.recent_transactions" :key="tx.id" class="bg-secondary/5 border-b border-white/5 hover:bg-secondary/10">
                <td class="px-6 py-4">{{ tx.id }}</td>
                <td class="px-6 py-4">{{ tx.user }}</td>
                <td class="px-6 py-4">{{ tx.game }}</td>
                <td class="px-6 py-4">Rp {{ tx.amount?.toLocaleString() || '0' }}</td>
                <td class="px-6 py-4">Rp {{ tx.profit?.toLocaleString() || '0' }}</td>
                <td class="px-6 py-4">
                  <span :class="{
                    'px-2 py-1 text-xs rounded-full': true,
                    'bg-green-700/30 text-green-200': tx.status === 'completed',
                    'bg-yellow-600/30 text-yellow-200': tx.status === 'pending',
                    'bg-blue-600/30 text-blue-200': tx.status === 'processing',
                    'bg-red-700/30 text-red-200': tx.status === 'failed' || tx.status === 'cancelled',
                  }">
                    {{ tx.status }}
                  </span>
                </td>
                <td class="px-6 py-4">{{ tx.date }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Flashsale Events Section -->
      <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
        <h3 class="mb-4 text-lg font-medium text-white">Flashsale Events</h3>
        <div v-if="flashsaleEvents.length === 0" class="text-center py-8 text-white/70">
          No active flashsale events found
        </div>
        <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div v-for="event in flashsaleEvents" :key="event.id" class="p-4 rounded-lg bg-secondary/5">
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold text-white">{{ event.event_name }}</h4>
              <span :class="{
                'px-2 py-1 text-xs rounded-full': true,
                'bg-green-700/30 text-green-200': new Date(event.event_end_date) > new Date(),
                'bg-red-700/30 text-red-200': new Date(event.event_end_date) <= new Date(),
              }">
                {{ new Date(event.event_end_date) > new Date() ? 'Active' : 'Ended' }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-2 mb-2 text-sm">
              <div class="text-white/70">Start:</div>
              <div class="text-white">{{ new Date(event.event_start_date).toLocaleString() }}</div>
              <div class="text-white/70">End:</div>
              <div class="text-white">{{ new Date(event.event_end_date).toLocaleString() }}</div>
              <div class="text-white/70">Revenue:</div>
              <div class="text-white">Rp {{ event.total_revenue?.toLocaleString() || '0' }}</div>
            </div>
            <div class="mt-4">
              <h5 class="text-sm font-medium text-secondary mb-2">Top Items:</h5>
              <div v-if="event.top_items?.length" class="space-y-2">
                <div v-for="item in event.top_items" :key="item.id" class="flex justify-between text-sm">
                  <span class="text-white">{{ item.service_name }}</span>
                  <span class="text-white/70">{{ item.sold }} sold</span>
                </div>
              </div>
              <div v-else class="text-sm text-white/70">No items sold yet</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Voucher Utilization Section -->
      <div class="p-6 rounded-lg shadow-lg bg-secondary/10">
        <h3 class="mb-4 text-lg font-medium text-white">Voucher Utilization</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-white">
            <thead class="text-xs uppercase bg-secondary/20">
              <tr>
                <th scope="col" class="px-6 py-3">Code</th>
                <th scope="col" class="px-6 py-3">Value</th>
                <th scope="col" class="px-6 py-3">Usage</th>
                <th scope="col" class="px-6 py-3">Limit</th>
                <th scope="col" class="px-6 py-3">Utilization</th>
                <th scope="col" class="px-6 py-3">Expires</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="voucherData.length === 0">
                <td colspan="6" class="px-6 py-4 text-center">No active vouchers found</td>
              </tr>
              <tr v-for="voucher in voucherData" :key="voucher.id" class="bg-secondary/5 border-b border-white/5 hover:bg-secondary/10">
                <td class="px-6 py-4 font-medium">{{ voucher.kode_voucher }}</td>
                <td class="px-6 py-4">{{ voucher.nilai }}</td>
                <td class="px-6 py-4">{{ voucher.usage_count }}</td>
                <td class="px-6 py-4">{{ voucher.usage_limit || 'Unlimited' }}</td>
                <td class="px-6 py-4">
                  <div class="w-full bg-secondary/20 rounded-full h-2.5 dark:bg-gray-700">
                    <div class="bg-secondary h-2.5 rounded-full" :style="`width: ${voucher.utilization_pct}%`"></div>
                  </div>
                  <span class="text-xs mt-1 inline-block">{{ voucher.utilization_pct.toFixed(0) }}%</span>
                </td>
                <td class="px-6 py-4">{{ voucher.expired_at ? new Date(voucher.expired_at).toLocaleDateString() : 'Never' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
