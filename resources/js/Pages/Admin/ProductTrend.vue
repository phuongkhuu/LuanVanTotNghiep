<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import Chart from 'chart.js/auto';

const props = defineProps({
    selectedProduct: [Number, String],
    currentPeriod: String,
});

const productId = ref(props.selectedProduct || '');
const period = ref(props.currentPeriod || 'week');
const periods = ['day', 'week', 'month', 'year'];
const isLoading = ref(false);
const searchKeyword = ref('');
const searchResults = ref([]);
const showSuggestions = ref(false);
let searchTimeout = null;

// Refs cho canvas
const revenueChartRef = ref(null);
const quantityChartRef = ref(null);

let revenueChart = null;
let quantityChart = null;

const revenueChartData = ref({
    labels: [],
    datasets: [
        {
            label: 'Doanh thu (VNĐ)',
            data: [],
            borderColor: '#ff6b00',
            backgroundColor: 'rgba(255, 107, 0, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ff6b00',
            pointRadius: 3,
        }
    ]
});

const quantityChartData = ref({
    labels: [],
    datasets: [
        {
            label: 'Số lượng bán',
            data: [],
            borderColor: '#1a56db',
            backgroundColor: 'rgba(26, 86, 219, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#1a56db',
            pointRadius: 3,
        }
    ]
});

const formatPrice = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0₫';
    return Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '₫';
};

const formatProductDisplay = (product) => {
    return product.name + (product.brand ? ` (${product.brand.name})` : '');
};

const searchProducts = async () => {
    const keyword = searchKeyword.value.trim();
    if (keyword.length < 2) {
        searchResults.value = [];
        showSuggestions.value = false;
        return;
    }

    try {
        const response = await axios.get(route('admin.reports.search-products'), {
            params: { q: keyword }
        });
        searchResults.value = response.data || [];
        showSuggestions.value = searchResults.value.length > 0;
    } catch (error) {
        console.error('Lỗi tìm kiếm sản phẩm:', error);
        searchResults.value = [];
        showSuggestions.value = false;
    }
};

const selectProduct = (product) => {
    productId.value = product.id;
    searchKeyword.value = formatProductDisplay(product);
    showSuggestions.value = false;
    searchResults.value = [];
};

const loadData = async () => {
    if (!productId.value) {
        revenueChartData.value = { labels: [], datasets: [{ ...revenueChartData.value.datasets[0], data: [] }] };
        quantityChartData.value = { labels: [], datasets: [{ ...quantityChartData.value.datasets[0], data: [] }] };
        updateCharts();
        return;
    }
    isLoading.value = true;
    try {
        const response = await axios.get(route('admin.reports.product-trend-data'), {
            params: {
                product_id: productId.value,
                period: period.value
            }
        });
        const revenueData = response.data.revenue || [];
        const quantityData = response.data.quantity || [];
        
        console.log('📊 Dữ liệu doanh thu:', revenueData);
        console.log('📊 Dữ liệu số lượng:', quantityData);

        revenueChartData.value = {
            labels: revenueData.map(item => item.label),
            datasets: [{ ...revenueChartData.value.datasets[0], data: revenueData.map(item => item.revenue) }]
        };
        quantityChartData.value = {
            labels: quantityData.map(item => item.label),
            datasets: [{ ...quantityChartData.value.datasets[0], data: quantityData.map(item => item.quantity) }]
        };
        updateCharts();
    } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

const updateCharts = () => {
    if (revenueChart) {
        revenueChart.data.labels = revenueChartData.value.labels;
        revenueChart.data.datasets[0].data = revenueChartData.value.datasets[0].data;
        revenueChart.update();
    }
    if (quantityChart) {
        quantityChart.data.labels = quantityChartData.value.labels;
        quantityChart.data.datasets[0].data = quantityChartData.value.datasets[0].data;
        quantityChart.update();
    }
};

const initCharts = () => {
    // Biểu đồ doanh thu
    const revenueCanvas = revenueChartRef.value;
    if (revenueCanvas) {
        if (revenueChart) {
            revenueChart.destroy();
            revenueChart = null;
        }
        revenueChart = new Chart(revenueCanvas.getContext('2d'), {
            type: 'line',
            data: revenueChartData.value,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 10, weight: 'bold' } } },
                    tooltip: {
                        titleFont: { size: 11 },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + formatPrice(context.raw || 0);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'VNĐ', font: { size: 9, weight: 'bold' } },
                        ticks: {
                            font: { size: 9 },
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                return value;
                            }
                        }
                    },
                    x: {
                        ticks: { font: { size: 9 } }
                    }
                }
            }
        });
    }

    // Biểu đồ số lượng
    const quantityCanvas = quantityChartRef.value;
    if (quantityCanvas) {
        if (quantityChart) {
            quantityChart.destroy();
            quantityChart = null;
        }
        quantityChart = new Chart(quantityCanvas.getContext('2d'), {
            type: 'line',
            data: quantityChartData.value,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 10, weight: 'bold' } } },
                    tooltip: {
                        titleFont: { size: 11 },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: function(context) {
                                return 'Số lượng: ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Số lượng', font: { size: 9, weight: 'bold' } },
                        ticks: { font: { size: 9 } }
                    },
                    x: {
                        ticks: { font: { size: 9 } }
                    }
                }
            }
        });
    }
};

const closeSuggestions = () => {
    window.setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

watch([productId, period], () => {
    loadData();
});

watch(searchKeyword, (newVal) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(() => {
        searchProducts();
    }, 400);
});

onMounted(async () => {
    await nextTick();
    initCharts();

    if (productId.value) {
        try {
            const response = await axios.get(route('admin.reports.search-products'), {
                params: { q: productId.value }
            });
            const found = response.data.find(p => p.id == productId.value);
            if (found) {
                searchKeyword.value = formatProductDisplay(found);
            }
        } catch (e) {
            console.warn('Không lấy được tên sản phẩm từ ID:', e);
        }
        loadData();
    }
});

onUnmounted(() => {
    if (revenueChart) {
        revenueChart.destroy();
        revenueChart = null;
    }
    if (quantityChart) {
        quantityChart.destroy();
        quantityChart = null;
    }
    if (searchTimeout) clearTimeout(searchTimeout);
});
</script>

<template>
    <Head title="Xu hướng sản phẩm - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-3 md:p-4">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h1 class="text-base font-bold text-gray-800">Xu hướng doanh thu sản phẩm</h1>
                <div class="flex items-center gap-2 flex-wrap">
                    <select v-model="period" class="px-5 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 pr-8">
                        <option v-for="p in periods" :key="p" :value="p">
                            {{ p === 'day' ? 'Ngày' : p === 'week' ? 'Tuần' : p === 'month' ? 'Tháng' : 'Năm' }}
                        </option>
                    </select>
                    <div class="relative">
                        <input
                            type="text"
                            v-model="searchKeyword"
                            placeholder="Nhập tên sản phẩm..."
                            class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 w-56"
                            @focus="showSuggestions = searchResults.length > 0"
                            @blur="closeSuggestions"
                        />
                        <div v-if="showSuggestions && searchResults.length > 0" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded shadow-lg z-10 max-h-56 overflow-y-auto">
                            <div
                                v-for="product in searchResults"
                                :key="product.id"
                                @mousedown.prevent="selectProduct(product)"
                                class="px-3 py-2 hover:bg-orange-50 cursor-pointer text-sm flex items-center justify-between"
                            >
                                <span>{{ product.name }}</span>
                                <span v-if="product.brand" class="text-xs text-gray-400">{{ product.brand.name }}</span>
                            </div>
                            <div v-if="searchResults.length === 0" class="px-3 py-2 text-sm text-gray-400 text-center">
                                Không tìm thấy
                            </div>
                        </div>
                    </div>

                    <div v-if="productId && searchKeyword" class="flex items-center gap-1 text-sm bg-orange-50 px-3 py-1 rounded-full border border-orange-200">
                        <span class="text-orange-600 font-medium truncate max-w-[150px]">{{ searchKeyword }}</span>
                        <button @click="productId = ''; searchKeyword = ''" class="text-gray-400 hover:text-red-500">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Nội dung chính -->
            <div class="bg-white rounded-lg p-3 border border-gray-100 shadow-sm">
                <div v-if="!productId" class="text-center py-6 text-gray-400 text-base">
                    <span class="material-symbols-outlined text-4xl">bar_chart</span>
                    <p class="mt-2">Nhập và chọn sản phẩm</p>
                </div>

                <!-- Biểu đồ doanh thu -->
                <div v-show="productId" class="w-full h-[250px] relative mb-3">
                    <canvas ref="revenueChartRef"></canvas>
                </div>

                <!-- Biểu đồ số lượng -->
                <div v-show="productId" class="w-full h-[250px] relative">
                    <canvas ref="quantityChartRef"></canvas>
                </div>

                <div v-if="productId && revenueChartData.datasets[0].data.every(v => v === 0)" class="text-center text-gray-400 text-sm mt-2">
                    Không có dữ liệu doanh thu cho sản phẩm này
                </div>

                <div v-if="isLoading" class="flex justify-center py-4">
                    <div class="inline-block w-6 h-6 border-3 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>