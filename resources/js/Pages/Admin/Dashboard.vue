
<template>
  <AdminLayout title="Dashboard">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-100">Admin Dashboard</h1>

        <!-- Period selector -->
        <div class="mt-4 bg-header-background p-4 rounded-lg shadow-lg">
          <div class="flex flex-wrap gap-3 mb-4">
            <button
              v-for="period in periods"
              :key="period.value"
              @click="changePeriod(period.value)"
              :class="[
                'px-4 py-2 rounded-full text-sm font-medium transition-all',
                activePeriod === period.value
                  ? 'bg-primary text-white shadow-lg shadow-primary/20'
                  : 'bg-header-background text-gray-300 hover:bg-gray-700'
              ]"
            >
              {{ period.label }}
            </button>

            <!-- Custom date range picker placeholder -->
            <button 
              @click="showCustomDatePicker = !showCustomDatePicker"
              class="px-4 py-2 rounded-full text-sm font-medium bg-header-background text-gray-300 hover:bg-gray-700 transition-all flex items-center gap-2"
            >
              <span>Custom Range</span>
              <span v-if="customDateActive" class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
            </button>
          </div>

          <!-- Custom date picker (simplified) -->
          <div v-if="showCustomDatePicker" class="bg-content-background p-4 rounded-lg mb-4 flex gap-4 flex-wrap">
            <div>
              <label class="block text-sm text-gray-400 mb-1">Start Date</label>
              <input type="date" v-model="dateRange.start" class="bg-gray-700 text-white rounded px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm text-gray-400 mb-1">End Date</label>
              <input type="date" v-model="dateRange.end" class="bg-gray-700 text-white rounded px-3 py-2" />
            </div>
            <div class="flex items-end">
              <button 
                @click="applyCustomDateRange" 
                class="px-4 py-2 bg-secondary text-white rounded-lg hover:bg-opacity-80 transition-all"
              >
                Apply
              </button>
            </div>
          </div>
        </div>

        <!-- Key metrics -->
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Users metric -->
          <div class="bg-header-background overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-primary/10 rounded-md p-3">
                  <svg class="h-6 w-6 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dt class="text-sm font-medium text-gray-400 truncate">
                    Total Users
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-white">
                      {{ metrics.users.total.toLocaleString() }}
                    </div>

                    <div 
                      :class="[
                        'ml-2 flex items-baseline text-sm font-semibold',
                        metrics.users.isPositive ? 'text-green-400' : 'text-red-500'
                      ]"
                    >
                      <svg 
                        v-if="metrics.users.isPositive" 
                        class="self-center flex-shrink-0 h-5 w-5 text-green-500" 
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"
                      >
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <svg 
                        v-else 
                        class="self-center flex-shrink-0 h-5 w-5 text-red-500" 
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"
                      >
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span>{{ metrics.users.growthPercent }}%</span>
                    </div>
                  </dd>
                </div>
              </div>
            </div>
            <div class="bg-header-background px-5 py-3">
              <div class="text-sm">
                <span class="font-medium text-secondary">
                  {{ metrics.users.newUsers }} new users
                </span>
                <span class="text-gray-400"> in this period</span>
              </div>
            </div>
          </div>

          <!-- Revenue metric -->
          <div class="bg-header-background overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-secondary/10 rounded-md p-3">
                  <svg class="h-6 w-6 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dt class="text-sm font-medium text-gray-400 truncate">
                    Revenue
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-white">
                      ${{ metrics.revenue.total.toLocaleString() }}
                    </div>

                    <div 
                      :class="[
                        'ml-2 flex items-baseline text-sm font-semibold',
                        metrics.revenue.isPositive ? 'text-green-400' : 'text-red-500'
                      ]"
                    >
                      <svg 
                        v-if="metrics.revenue.isPositive" 
                        class="self-center flex-shrink-0 h-5 w-5 text-green-500" 
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"
                      >
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <svg 
                        v-else 
                        class="self-center flex-shrink-0 h-5 w-5 text-red-500" 
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"
                      >
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span>{{ metrics.revenue.growthPercent }}%</span>
                    </div>
                  </dd>
                </div>
              </div>
            </div>
            <div class="bg-header-background px-5 py-3">
              <div class="text-sm">
                <span class="font-medium text-secondary">
                  {{ period }} performance
                </span>
                <span class="text-gray-400"> vs previous period</span>
              </div>
            </div>
          </div>

          <!-- Orders metric -->
          <div class="bg-header-background overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-primary/10 rounded-md p-3">
                  <svg class="h-6 w-6 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dt class="text-sm font-medium text-gray-400 truncate">
                    Orders
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-white">
                      {{ metrics.orders.total.toLocaleString() }}
                    </div>

                    <div 
                      :class="[
                        'ml-2 flex items-baseline text-sm font-semibold',
                        metrics.orders.isPositive ? 'text-green-400' : 'text-red-500'
                      ]"
                    >
                      <svg 
                        v-if="metrics.orders.isPositive" 
                        class="self-center flex-shrink-0 h-5 w-5 text-green-500" 
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"
                      >
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <svg 
                        v-else 
                        class="self-center flex-shrink-0 h-5 w-5 text-red-500" 
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"
                      >
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span>{{ metrics.orders.growthPercent }}%</span>
                    </div>
                  </dd>
                </div>
              </div>
            </div>
            <div class="bg-header-background px-5 py-3">
              <div class="text-sm">
                <span class="font-medium text-secondary">
                  {{ period }} analysis
                </span>
                <span class="text-gray-400"> compared to previous</span>
              </div>
            </div>
          </div>

          <!-- Products metric -->
          <div class="bg-header-background overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-secondary/10 rounded-md p-3">
                  <svg class="h-6 w-6 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dt class="text-sm font-medium text-gray-400 truncate">
                    Active Products
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-white">
                      {{ metrics.products.total.toLocaleString() }}
                    </div>

                    <div 
                      :class="[
                        'ml-2 flex items-baseline text-sm font-semibold',
                        metrics.products.isPositive ? 'text-green-400' : 'text-red-500'
                      ]"
                    >
                      <svg 
                        v-if="metrics.products.isPositive" 
                        class="self-center flex-shrink-0 h-5 w-5 text-green-500" 
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"
                      >
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <svg 
                        v-else 
                        class="self-center flex-shrink-0 h-5 w-5 text-red-500" 
                        fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"
                      >
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span>{{ metrics.products.growthPercent }}%</span>
                    </div>
                  </dd>
                </div>
              </div>
            </div>
            <div class="bg-header-background px-5 py-3">
              <div class="text-sm">
                <span class="font-medium text-secondary">
                  {{ period }} growth
                </span>
                <span class="text-gray-400"> vs previous period</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Revenue / Profit / Failed Orders Chart -->
          <div class="bg-header-background rounded-lg shadow overflow-hidden">
            <div class="p-5">
              <h3 class="text-lg font-medium text-gray-100">Revenue & Profit Trend</h3>
              <canvas ref="revenueChart" class="mt-4 h-80"></canvas>
            </div>
          </div>

          <!-- Order Status Distribution -->
          <div class="bg-header-background rounded-lg shadow overflow-hidden">
            <div class="p-5">
              <h3 class="text-lg font-medium text-gray-100">Order Status Distribution</h3>
              <div class="mt-4 flex justify-center">
                <canvas ref="orderStatsChart" class="h-64"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Flashsales Section -->
        <div v-if="activeFlashsales.length > 0" class="mt-8">
          <h3 class="text-xl font-medium text-gray-100 mb-4">Active Flashsales</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="flashsale in activeFlashsales" 
              :key="flashsale.id"
              class="bg-header-background rounded-lg shadow overflow-hidden relative"
            >
              <!-- Cosmic particle effect overlay -->
              <div class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-secondary/5"></div>
                <div class="cosmic-particles"></div>
              </div>

              <div class="relative p-4 z-10">
                <div class="flex justify-between items-start">
                  <h4 class="text-lg font-semibold text-white">{{ flashsale.event_name }}</h4>
                  <div class="px-2 py-1 bg-primary/20 rounded-full text-xs text-primary">
                    {{ getRemainingTime(flashsale) }}
                  </div>
                </div>

                <div class="mt-3 space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Items:</span>
                    <span class="text-white font-medium">{{ flashsale.item_count }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Total Sales:</span>
                    <span class="text-white font-medium">${{ flashsale.total_sales?.toLocaleString() || 0 }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Total Profit:</span>
                    <span class="text-white font-medium">${{ flashsale.total_profit?.toLocaleString() || 0 }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Conversion Rate:</span>
                    <span class="text-white font-medium">{{ flashsale.conversion_rate || '0%' }}</span>
                  </div>
                </div>

                <div class="mt-4">
                  <div class="h-1 w-full bg-gray-700 rounded-full overflow-hidden">
                    <div 
                      class="h-full bg-secondary rounded-full" 
                      :style="`width: ${getFlashsaleProgress(flashsale)}%`"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Products Section with service details -->
        <div class="mt-8">
          <h3 class="text-xl font-medium text-gray-100 mb-4">Top Products</h3>
          
          <div class="bg-header-background rounded-lg shadow overflow-hidden">
            <div class="flex flex-col-reverse md:flex-row items-center justify-between p-4">
              <div class="text-sm text-gray-400 mt-2 md:mt-0">
                Showing top 5 products by sales volume
              </div>
              
              <div class="flex gap-2">
                <button 
                  @click="resetProductFilter" 
                  v-if="selectedProduct"
                  class="px-3 py-1 text-xs bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-all"
                >
                  Show All Products
                </button>
                
                <button 
                  @click="exportProductData" 
                  class="px-3 py-1 text-xs bg-secondary text-white rounded-md hover:bg-secondary/80 transition-all flex items-center gap-1"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Export
                </button>
              </div>
            </div>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-800">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Product Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Sales
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Revenue
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Profit
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Growth
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                      <span class="sr-only">Details</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-header-background divide-y divide-gray-700">
                  <tr v-for="product in selectedProduct ? [selectedProduct] : tables.top_products" :key="product.id" class="hover:bg-gray-800">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div>
                          <div class="text-sm font-medium text-white">
                            {{ product.name }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">{{ product.sales }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">${{ product.revenue.toLocaleString() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">${{ product.profit.toLocaleString() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${product.growth > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`">
                        {{ product.growth > 0 ? '+' : '' }}{{ product.growth }}%
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button 
                        v-if="!selectedProduct"
                        @click="selectProduct(product)" 
                        class="text-secondary hover:text-secondary/80"
                      >
                        View Services
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Product Services Detail (when a product is selected) -->
        <div v-if="selectedProduct && productServices.length > 0" class="mt-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-100">
              Services for {{ selectedProduct.name }}
            </h3>
            <button 
              @click="exportServiceData" 
              class="px-3 py-1 text-xs bg-secondary text-white rounded-md hover:bg-secondary/80 transition-all flex items-center gap-1"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export Services
            </button>
          </div>
          
          <div class="bg-header-background rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-800">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Service Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Orders
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Revenue
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Profit
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-header-background divide-y divide-gray-700">
                  <tr v-for="service in productServices" :key="service.id" class="hover:bg-gray-800">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-white">
                        {{ service.name }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">{{ service.orders }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">${{ service.revenue.toLocaleString() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">${{ service.profit.toLocaleString() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`">
                        {{ service.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="mt-8">
          <h3 class="text-xl font-medium text-gray-100 mb-4">Recent Transactions</h3>
          
          <div class="bg-header-background rounded-lg shadow overflow-hidden">
            <div class="flex justify-end p-4">              
              <button 
                @click="exportTransactionData" 
                class="px-3 py-1 text-xs bg-secondary text-white rounded-md hover:bg-secondary/80 transition-all flex items-center gap-1"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Transactions
              </button>
            </div>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-800">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      User
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Game
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Amount
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Profit
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Date
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-header-background divide-y divide-gray-700">
                  <tr v-for="transaction in tables.recent_transactions" :key="transaction.id" class="hover:bg-gray-800">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">{{ transaction.id }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">{{ transaction.user }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">{{ transaction.game }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">${{ transaction.amount.toLocaleString() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">${{ transaction.profit?.toLocaleString() || '0' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                          transaction.status === 'completed' ? 'bg-green-100 text-green-800' : 
                          transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                          transaction.status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                          'bg-red-100 text-red-800'
                        }`"
                      >
                        {{ transaction.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">
                      {{ transaction.date }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Voucher Performance Section -->
        <div class="mt-8">
          <h3 class="text-xl font-medium text-gray-100 mb-4">Active Vouchers</h3>
          
          <div v-if="activeVouchers.length" class="bg-header-background rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-800">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Voucher Code
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Discount
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Usage
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Effectiveness
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-header-background divide-y divide-gray-700">
                  <tr v-for="voucher in activeVouchers" :key="voucher.id" class="hover:bg-gray-800">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-white">{{ voucher.code }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="voucher.type === 'percentage'" class="text-sm text-white">
                        {{ voucher.value }}% off
                      </div>
                      <div v-else class="text-sm text-white">
                        ${{ voucher.value }} off
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="text-sm text-white mr-2">
                          {{ voucher.usage_count }} / {{ voucher.usage_limit || '∞' }}
                        </div>
                        <div v-if="voucher.usage_limit" class="w-16 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                          <div 
                            class="h-full bg-secondary rounded-full" 
                            :style="`width: ${(voucher.usage_count / voucher.usage_limit) * 100}%`"
                          ></div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-white">{{ voucher.effectiveness || 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <div v-else class="bg-header-background rounded-lg shadow p-8 text-center text-gray-400">
            No active vouchers found
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

Chart.register(...registerables);

// Props from controller
const props = defineProps({
  metrics: {
    type: Object,
    required: true
  },
  charts: {
    type: Object,
    required: true
  },
  tables: {
    type: Object,
    required: true
  },
  period: {
    type: String,
    required: true,
    default: 'week'
  }
});

// State variables
const revenueChart = ref(null);
const orderStatsChart = ref(null);
const activePeriod = ref(props.period);
const selectedProduct = ref(null);
const productServices = ref([]);
const activeFlashsales = ref([]);
const activeVouchers = ref([]);
const showCustomDatePicker = ref(false);
const customDateActive = ref(false);
const dateRange = ref({
  start: '',
  end: ''
});

// Available time periods
const periods = [
  { label: 'Today', value: 'day' },
  { label: 'This Week', value: 'week' },
  { label: 'This Month', value: 'month' },
  { label: 'This Year', value: 'year' },
];

// Chart instances for cleanup
let revenueChartInstance = null;
let orderStatsChartInstance = null;

// Load data on component mount
onMounted(async () => {
  initCharts();
  await loadFlashsales();
  await loadVouchers();
});

// Watch for period changes to update charts
watch(() => props.charts, () => {
  updateCharts();
}, { deep: true });

// Initialize charts
function initCharts() {
  // Revenue chart initialization
  if (revenueChart.value) {
    const ctx = revenueChart.value.getContext('2d');
    
    revenueChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: props.charts.revenue_trend.labels,
        datasets: props.charts.revenue_trend.datasets
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            grid: {
              color: 'rgba(255, 255, 255, 0.1)',
            },
            ticks: {
              color: 'rgba(255, 255, 255, 0.7)',
            }
          },
          y: {
            grid: {
              color: 'rgba(255, 255, 255, 0.1)',
            },
            ticks: {
              color: 'rgba(255, 255, 255, 0.7)',
              callback: function(value) {
                return '$' + value.toLocaleString();
              }
            }
          }
        },
        plugins: {
          tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.parsed.y !== null) {
                  label += '$' + context.parsed.y.toLocaleString();
                }
                return label;
              }
            }
          },
          legend: {
            labels: {
              color: 'rgba(255, 255, 255, 0.7)'
            }
          }
        }
      }
    });
  }

  // Order stats chart initialization
  if (orderStatsChart.value && props.charts.order_stats?.statusDistribution) {
    const ctx = orderStatsChart.value.getContext('2d');
    
    orderStatsChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: props.charts.order_stats.statusDistribution,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: 'rgba(255, 255, 255, 0.7)',
              padding: 20,
              font: {
                size: 12
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = total ? Math.round((value / total) * 100) : 0;
                return `${context.label}: ${value} (${percentage}%)`;
              }
            }
          }
        },
        animation: {
          animateScale: true,
          animateRotate: true
        }
      }
    });
  }
}

// Update charts with new data
function updateCharts() {
  if (revenueChartInstance && props.charts.revenue_trend) {
    revenueChartInstance.data.labels = props.charts.revenue_trend.labels;
    revenueChartInstance.data.datasets = props.charts.revenue_trend.datasets;
    revenueChartInstance.update();
  }
  
  if (orderStatsChartInstance && props.charts.order_stats?.statusDistribution) {
    orderStatsChartInstance.data = props.charts.order_stats.statusDistribution;
    orderStatsChartInstance.update();
  }
}

// Load active flashsales
async function loadFlashsales() {
  try {
    // In a real app, this would be an API call
    // Simulating response for now
    activeFlashsales.value = [
      {
        id: 1,
        event_name: 'Summer Gaming Fest',
        event_start_date: new Date(Date.now() - 1000 * 60 * 60 * 24), // 1 day ago
        event_end_date: new Date(Date.now() + 1000 * 60 * 60 * 48),   // 2 days from now
        item_count: 15,
        total_sales: 4920,
        total_profit: 1200,
        conversion_rate: '24%'
      },
      {
        id: 2,
        event_name: 'Weekend Deals',
        event_start_date: new Date(Date.now()),
        event_end_date: new Date(Date.now() + 1000 * 60 * 60 * 72), // 3 days from now
        item_count: 8,
        total_sales: 1560,
        total_profit: 420,
        conversion_rate: '18%'
      }
    ];
  } catch (error) {
    console.error('Error loading flashsales:', error);
  }
}

// Load active vouchers
async function loadVouchers() {
  try {
    // Simulating response for now
    activeVouchers.value = [
      {
        id: 1,
        code: 'SUMMER25',
        type: 'percentage',
        value: 25,
        usage_count: 45,
        usage_limit: 100,
        effectiveness: '18% increase in AOV'
      },
      {
        id: 2,
        code: 'NEWUSER10',
        type: 'fixed',
        value: 10,
        usage_count: 78,
        usage_limit: null,
        effectiveness: '22% conversion rate'
      }
    ];
  } catch (error) {
    console.error('Error loading vouchers:', error);
  }
}

// Select a product to view its services
async function selectProduct(product) {
  selectedProduct.value = product;
  
  try {
    // In a real app, this would be an API call
    // Simulating response for now based on the selected product
    productServices.value = [
      {
        id: 1,
        name: `${product.name} - 100 Coins`,
        orders: Math.floor(product.sales * 0.4),
        revenue: Math.floor(product.revenue * 0.4),
        profit: Math.floor(product.profit * 0.4),
        status: 'active'
      },
      {
        id: 2,
        name: `${product.name} - 500 Coins`,
        orders: Math.floor(product.sales * 0.3),
        revenue: Math.floor(product.revenue * 0.3),
        profit: Math.floor(product.profit * 0.3),
        status: 'active'
      },
      {
        id: 3,
        name: `${product.name} - 1000 Coins`,
        orders: Math.floor(product.sales * 0.2),
        revenue: Math.floor(product.revenue * 0.2),
        profit: Math.floor(product.profit * 0.2),
        status: 'active'
      },
      {
        id: 4,
        name: `${product.name} - 2000 Coins`,
        orders: Math.floor(product.sales * 0.1),
        revenue: Math.floor(product.revenue * 0.1),
        profit: Math.floor(product.profit * 0.1),
        status: 'inactive'
      }
    ];
  } catch (error) {
    console.error('Error loading product services:', error);
  }
}

// Reset product filter to show all products
function resetProductFilter() {
  selectedProduct.value = null;
  productServices.value = [];
}

// Change time period and reload data
function changePeriod(period) {
  activePeriod.value = period;
  customDateActive.value = false;
  
  // Navigate with new period param to reload data
  router.visit(route('admin.dashboard', { period }), {
    preserveScroll: true,
    preserveState: true,
    only: ['metrics', 'charts', 'tables', 'period']
  });
}

// Apply custom date range
function applyCustomDateRange() {
  if (!dateRange.value.start || !dateRange.value.end) {
    // Handle validation error
    alert('Please select both start and end dates');
    return;
  }
  
  customDateActive.value = true;
  showCustomDatePicker.value = false;
  
  // In a real app, this would send the custom range to the server
  // For now, we'll just simulate this by using the week period
  router.visit(route('admin.dashboard', { period: 'week' }), {
    preserveScroll: true,
    preserveState: true,
    only: ['metrics', 'charts', 'tables', 'period']
  });
}

// Get remaining time for flashsale
function getRemainingTime(flashsale) {
  const now = new Date();
  const end = new Date(flashsale.event_end_date);
  const diffInMs = end - now;
  
  if (diffInMs < 0) return 'Expired';
  
  const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
  const diffInDays = Math.floor(diffInHours / 24);
  
  if (diffInDays > 0) {
    return `${diffInDays}d ${diffInHours % 24}h left`;
  }
  
  return `${diffInHours}h left`;
}

// Calculate flashsale progress percentage
function getFlashsaleProgress(flashsale) {
  const start = new Date(flashsale.event_start_date);
  const end = new Date(flashsale.event_end_date);
  const now = new Date();
  
  const totalDuration = end - start;
  const elapsed = now - start;
  
  return Math.min(100, Math.max(0, Math.round((elapsed / totalDuration) * 100)));
}

// Export data functions
function exportProductData() {
  // In a real app, this would trigger a download
  alert('Exporting product data to Excel...');
}

function exportServiceData() {
  // In a real app, this would trigger a download
  alert('Exporting service data to Excel...');
}

function exportTransactionData() {
  // In a real app, this would trigger a download
  alert('Exporting transaction data to Excel...');
}
</script>

<style scoped>
.cosmic-particles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 25% 30%, rgba(155, 135, 245, 0.15) 2px, transparent 2px),
    radial-gradient(circle at 75% 60%, rgba(51, 195, 240, 0.15) 3px, transparent 3px),
    radial-gradient(circle at 50% 80%, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
  background-size: 180px 180px, 150px 150px, 100px 100px;
  animation: cosmic-drift 50s linear infinite;
  pointer-events: none;
}

@keyframes cosmic-drift {
  0% { background-position: 0 0, 0 0, 0 0; }
  100% { background-position: 180px 180px, 150px -150px, -100px 100px; }
}

/* Responsive utilities */
@media (max-width: 640px) {
  .cosmic-particles {
    background-size: 90px 90px, 75px 75px, 50px 50px;
  }
}
</style>

