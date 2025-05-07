
/**
 * Service for Admin Dashboard operations
 */
export const AdminDashboardService = {
    /**
     * Format numbers with comma separators
     * @param {number} value - The number to format
     * @returns {string} - Formatted number
     */
    formatNumber(value) {
        return new Intl.NumberFormat('en-US').format(value);
    },

    /**
     * Format currency values
     * @param {number} value - The currency value
     * @param {string} currency - Currency code
     * @returns {string} - Formatted currency string
     */
    formatCurrency(value, currency = "USD") {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency,
        }).format(value);
    },

    /**
     * Prepare revenue chart data
     * @param {Array} revenueTrend - Raw revenue trend data
     * @returns {Object} - Formatted chart data
     */
    prepareRevenueChartData(revenueTrend) {
        const dates = revenueTrend.map(item => item.date);
        const revenues = revenueTrend.map(item => item.revenue);
        
        return {
            labels: dates,
            datasets: [
                {
                    label: 'Revenue',
                    data: revenues,
                    fill: true,
                    backgroundColor: 'rgba(155, 135, 245, 0.2)',
                    borderColor: '#9b87f5',
                    pointBackgroundColor: '#F97316',
                    pointBorderColor: '#ffffff',
                    pointHoverBackgroundColor: '#33C3F0',
                    tension: 0.4,
                }
            ]
        };
    },

    /**
     * Prepare order distribution chart data
     * @param {Array} orderDistribution - Raw order distribution data
     * @returns {Object} - Formatted chart data
     */
    prepareOrderDistributionChartData(orderDistribution) {
        const labels = orderDistribution.map(item => item.name);
        const data = orderDistribution.map(item => item.count);
        
        // Generate cosmic color palette
        const colors = this.generateCosmicColorPalette(labels.length);
        
        return {
            labels: labels,
            datasets: [
                {
                    data: data,
                    backgroundColor: colors.background,
                    borderColor: colors.border,
                    borderWidth: 1,
                    hoverBackgroundColor: colors.hover,
                }
            ]
        };
    },

    /**
     * Generate cosmic-themed color palette for charts
     * @param {number} count - Number of colors needed
     * @returns {Object} - Object with background, border and hover colors
     */
    generateCosmicColorPalette(count) {
        const baseColors = [
            { bg: 'rgba(155, 135, 245, 0.7)', border: '#9b87f5', hover: '#9b87f5' },
            { bg: 'rgba(51, 195, 240, 0.7)', border: '#33C3F0', hover: '#33C3F0' },
            { bg: 'rgba(249, 115, 22, 0.7)', border: '#F97316', hover: '#F97316' },
            { bg: 'rgba(139, 92, 246, 0.7)', border: '#8B5CF6', hover: '#8B5CF6' },
            { bg: 'rgba(16, 185, 129, 0.7)', border: '#10B981', hover: '#10B981' },
            { bg: 'rgba(245, 158, 11, 0.7)', border: '#F59E0B', hover: '#F59E0B' },
            { bg: 'rgba(236, 72, 153, 0.7)', border: '#EC4899', hover: '#EC4899' },
            { bg: 'rgba(6, 182, 212, 0.7)', border: '#06B6D4', hover: '#06B6D4' }
        ];
        
        // Create repeating pattern if more colors needed
        const backgrounds = [];
        const borders = [];
        const hovers = [];
        
        for (let i = 0; i < count; i++) {
            const colorIndex = i % baseColors.length;
            backgrounds.push(baseColors[colorIndex].bg);
            borders.push(baseColors[colorIndex].border);
            hovers.push(baseColors[colorIndex].hover);
        }
        
        return {
            background: backgrounds,
            border: borders,
            hover: hovers
        };
    },

    /**
     * Export dashboard data to CSV
     * @param {string} dataType - Type of data to export
     * @param {Object} data - Data to export
     */
    exportToCSV(dataType, data) {
        let csvContent = "";
        let filename = "";

        if (dataType === 'transactions') {
            filename = "recent-transactions.csv";
            // CSV Header
            csvContent = "Transaction ID,User,Game,Amount,Status,Date\n";
            
            // Add rows
            data.forEach(item => {
                csvContent += `${item.id},${item.user},${item.game},${item.amount},${item.status},${item.date}\n`;
            });
        } 
        else if (dataType === 'products') {
            filename = "top-products.csv";
            // CSV Header
            csvContent = "Product ID,Name,Sales,Revenue,Growth\n";
            
            // Add rows
            data.forEach(item => {
                csvContent += `${item.id},${item.name},${item.sales},${item.revenue},${item.growth}%\n`;
            });
        }

        // Create download link
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};
