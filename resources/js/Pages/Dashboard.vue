<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'

// Sidebar state
const sidebarCollapsed = ref(false)
const orderSubmenuOpen = ref(true)
const productSubmenuOpen = ref(false)
const customerSubmenuOpen = ref(false)

const toggleOrderSubmenu = () => { orderSubmenuOpen.value = !orderSubmenuOpen.value }
const toggleProductSubmenu = () => { productSubmenuOpen.value = !productSubmenuOpen.value }
const toggleCustomerSubmenu = () => { customerSubmenuOpen.value = !customerSubmenuOpen.value }

// Dữ liệu mẫu
const recentOrders = ref([
  { code: '#ORD-001', customer: 'Nguyễn Văn A', type: '🛒 Bán lẻ', amount: '2.500.000₫', status: 'Hoàn thành', statusClass: 'bg-[#2d6a4f]/10 text-[#2d6a4f]' },
  { code: '#ORD-002', customer: 'Công ty ABC', type: '🏭 Bán sỉ', amount: '18.500.000₫', status: 'Đang giao', statusClass: 'bg-[#ff6b00]/10 text-[#ff6b00]' },
  { code: '#ORD-003', customer: 'Trần Thị B', type: '⏳ Pre-order', amount: '1.850.000₫', status: 'Chờ xác nhận', statusClass: 'bg-[#f59e0b]/10 text-[#f59e0b]' }
])

const topRetail = ref([
  { name: 'Balo Doanh Nhân Elite', sold: 145 },
  { name: 'Túi Du Lịch Nomad', sold: 98 },
  { name: 'Balo Công Sở Commuter', sold: 87 }
])
const topWholesale = ref([
  { name: 'Balo Elite - Order số lượng lớn', quantity: 12 },
  { name: 'Túi Nomad - Doanh nghiệp', quantity: 8 },
  { name: 'Balo Tech Nova - Đối tác', quantity: 5 }
])
const topPreorder = ref([
  { name: 'Balo Limited Edition 2025', preordered: 45 },
  { name: 'Túi Da Cao Cấp', preordered: 32 },
  { name: 'Set Balo + Ví', preordered: 28 }
])

// Khởi tạo biểu đồ
onMounted(() => {
  const ctx = document.getElementById('revenueByTypeChart')?.getContext('2d')
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
        datasets: [
          { label: 'Bán lẻ', data: [8, 10, 7, 12, 15, 18, 14], backgroundColor: '#ff6b00', borderRadius: 6 },
          { label: 'Bán sỉ', data: [15, 18, 12, 22, 28, 35, 25], backgroundColor: '#436651', borderRadius: 6 },
          { label: 'Pre-order', data: [5, 4, 6, 8, 10, 12, 7], backgroundColor: '#f59e0b', borderRadius: 6 }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Doanh thu (triệu ₫)' } } }
      }
    })
  }
})

const logout = () => {
  router.post(route('logout'))
}
</script>

<template>
  <div class="flex min-h-screen bg-[#fbf9f5]">
    <!-- ==================== SIDEBAR ==================== -->
    <aside
      class="w-72 bg-white border-r border-[#e4e2de] fixed h-full overflow-y-auto z-20 transition-all"
      :class="{ '-ml-72': sidebarCollapsed }"
    >
      <div class="p-6 border-b border-[#e4e2de]">
        <h1 class="text-2xl font-bold text-[#ff6b00]">BigBag<span class="text-[#1b1c1a]">.vn</span></h1>
        <p class="text-xs text-[#56423d] mt-1">Admin Portal</p>
      </div>
      <nav class="p-4 space-y-1">
        <!-- Dashboard -->
        <Link :href="route('admin.dashboard')" class="flex items-center gap-3 px-4 py-3 rounded-lg sidebar-item-active text-[#ff6b00]">
          <span class="material-symbols-outlined">dashboard</span>
          <span class="flex-1 text-sm font-medium">Dashboard</span>
        </Link>

        <!-- Đơn hàng (có submenu) -->
        <div class="space-y-1">
          <div
            @click="toggleOrderSubmenu"
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] cursor-pointer transition-all"
          >
            <span class="material-symbols-outlined">receipt_long</span>
            <span class="flex-1 text-sm font-medium">Đơn hàng</span>
            <span
              class="material-symbols-outlined text-sm transition-transform"
              :class="{ 'rotate-180': orderSubmenuOpen }"
            >keyboard_arrow_down</span>
          </div>
          <div v-show="orderSubmenuOpen" class="ml-8 space-y-1">
            <Link :href="route('admin.orders', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] text-sm">
              🛒 Đơn bán lẻ
            </Link>
            <Link :href="route('admin.orders', { type: 'wholesale' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] text-sm">
              🏭 Đơn bán sỉ
            </Link>
            <Link :href="route('admin.orders', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] text-sm">
              ⏳ Đơn Pre-order
            </Link>
          </div>
        </div>

        <!-- Sản phẩm -->
        <div class="space-y-1">
          <div
            @click="toggleProductSubmenu"
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] cursor-pointer transition-all"
          >
            <span class="material-symbols-outlined">inventory_2</span>
            <span class="flex-1 text-sm font-medium">Sản phẩm</span>
            <span
              class="material-symbols-outlined text-sm transition-transform"
              :class="{ 'rotate-180': productSubmenuOpen }"
            >keyboard_arrow_down</span>
          </div>
          <div v-show="productSubmenuOpen" class="ml-8 space-y-1">
            <Link :href="route('admin.products', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] text-sm">
              🎒 Sản phẩm bán lẻ
            </Link>
            <Link :href="route('admin.products', { type: 'wholesale' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] text-sm">
              📦 Sản phẩm bán sỉ
            </Link>
            <Link :href="route('admin.products', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] text-sm">
              🔮 Sản phẩm Pre-order
            </Link>
          </div>
        </div>

        <!-- Khách hàng -->
        <div class="space-y-1">
          <div
            @click="toggleCustomerSubmenu"
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] cursor-pointer transition-all"
          >
            <span class="material-symbols-outlined">group</span>
            <span class="flex-1 text-sm font-medium">Khách hàng</span>
            <span
              class="material-symbols-outlined text-sm transition-transform"
              :class="{ 'rotate-180': customerSubmenuOpen }"
            >keyboard_arrow_down</span>
          </div>
          <div v-show="customerSubmenuOpen" class="ml-8 space-y-1">
            <Link :href="route('admin.customers', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] text-sm">
              👤 Khách hàng lẻ
            </Link>
            <Link :href="route('admin.customers', { type: 'business' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] text-sm">
              🏢 Khách hàng doanh nghiệp
            </Link>
          </div>
        </div>

        <Link :href="route('admin.customize')" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] transition-all">
          <span class="material-symbols-outlined">palette</span>
          <span class="flex-1 text-sm font-medium">Tùy chỉnh</span>
          <span class="text-xs bg-[#ff6b00] text-white px-2 py-0.5 rounded-full">3 mới</span>
        </Link>

        <Link :href="route('admin.promotions')" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] transition-all">
          <span class="material-symbols-outlined">local_offer</span>
          <span class="flex-1 text-sm font-medium">Khuyến mãi</span>
        </Link>

        <Link :href="route('admin.reports')" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] transition-all">
          <span class="material-symbols-outlined">bar_chart</span>
          <span class="flex-1 text-sm font-medium">Báo cáo</span>
        </Link>

        <Link :href="route('admin.settings')" class="flex items-center gap-3 px-4 py-3 rounded-lg text-[#56423d] hover:bg-[#fff5f2] hover:text-[#ff6b00] transition-all">
          <span class="material-symbols-outlined">settings</span>
          <span class="flex-1 text-sm font-medium">Cài đặt</span>
        </Link>
      </nav>

      <div class="absolute bottom-0 w-full p-4 border-t border-[#e4e2de] bg-white">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-[#ff6b00] flex items-center justify-center text-white font-bold text-lg">A</div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-[#1b1c1a]">Admin User</p>
            <p class="text-xs text-[#89726c]">admin@bigbag.vn</p>
          </div>
          <button @click="logout" class="text-[#56423d] hover:text-[#ff6b00]">
            <span class="material-symbols-outlined">logout</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Overlay cho mobile -->
    <div v-if="!sidebarCollapsed" @click="sidebarCollapsed = true" class="fixed inset-0 bg-black/50 z-10 lg:hidden"></div>

    <!-- ==================== MAIN CONTENT ==================== -->
    <main class="flex-1 lg:ml-72 transition-all" :class="{ 'lg:ml-0': sidebarCollapsed }">
      <header class="sticky top-0 z-10 glass-header border-b border-[#e4e2de] px-4 md:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
          <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-[#56423d] hover:text-[#ff6b00] lg:hidden">
            <span class="material-symbols-outlined">menu</span>
          </button>
          <div class="relative hidden md:block">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#89726c] text-lg">search</span>
            <input
              type="text"
              placeholder="Tìm kiếm..."
              class="pl-10 pr-4 py-2 bg-white border border-[#e4e2de] rounded-full w-80 focus:outline-none focus:border-[#ff6b00] focus:ring-2 focus:ring-[#ff6b00]/20 text-sm"
            />
          </div>
        </div>
        <div class="flex items-center gap-4">
          <button class="relative text-[#56423d] hover:text-[#ff6b00]">
            <span class="material-symbols-outlined">notifications</span>
            <span class="absolute -top-1 -right-1 w-4 h-4 bg-[#ff6b00] text-white text-[9px] rounded-full flex items-center justify-center">3</span>
          </button>
          <div class="flex gap-1 bg-[#e4e2de] rounded-full p-1">
            <button class="px-3 py-1 text-xs font-semibold rounded-full bg-[#ff6b00] text-white">VI</button>
            <button class="px-3 py-1 text-xs font-semibold rounded-full text-[#56423d]">EN</button>
          </div>
          <div class="flex items-center gap-2 pl-2 border-l border-[#e4e2de]">
            <div class="w-8 h-8 rounded-full bg-[#ff6b00] flex items-center justify-center text-white font-bold text-sm">A</div>
            <span class="text-sm font-medium text-[#1b1c1a] hidden md:block">Admin</span>
          </div>
        </div>
      </header>

      <!-- DASHBOARD CONTENT -->
      <div class="p-4 md:p-8">
        <!-- Welcome -->
        <div class="mb-8">
          <h1 class="text-2xl md:text-3xl font-bold text-[#1b1c1a]">Chào mừng trở lại, Admin</h1>
          <p class="text-[#56423d] text-sm mt-1">Đây là tổng quan hoạt động kinh doanh hôm nay</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="stat-card bg-white rounded-xl p-5 border-l-4 border-l-[#ff6b00] shadow-sm">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-[#56423d] text-sm font-medium">🛒 Bán lẻ hôm nay</p>
                <p class="text-2xl md:text-3xl font-bold text-[#1b1c1a] mt-2">8.250.000₫</p>
                <p class="text-xs text-[#2d6a4f] mt-2"><span class="material-symbols-outlined text-sm align-middle">trending_up</span> +8.2%</p>
              </div>
              <div class="w-12 h-12 rounded-full bg-[#fff5f2] flex items-center justify-center">
                <span class="material-symbols-outlined text-[#ff6b00] text-2xl">shopping_cart</span>
              </div>
            </div>
            <div class="mt-3 pt-2 border-t">
              <div class="flex justify-between text-xs text-[#89726c]"><span>📦 12 đơn hàng</span><span>⭐ 4.8 rating</span></div>
            </div>
          </div>

          <div class="stat-card bg-white rounded-xl p-5 border-l-4 border-l-[#436651] shadow-sm">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-[#56423d] text-sm font-medium">🏭 Bán sỉ hôm nay</p>
                <p class="text-2xl md:text-3xl font-bold text-[#1b1c1a] mt-2">32.500.000₫</p>
                <p class="text-xs text-[#2d6a4f] mt-2"><span class="material-symbols-outlined text-sm align-middle">trending_up</span> +23.5%</p>
              </div>
              <div class="w-12 h-12 rounded-full bg-[#436651]/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-[#436651] text-2xl">business</span>
              </div>
            </div>
            <div class="mt-3 pt-2 border-t">
              <div class="flex justify-between text-xs text-[#89726c]"><span>🏢 3 doanh nghiệp</span><span>📦 450 sản phẩm</span></div>
            </div>
          </div>

          <div class="stat-card bg-white rounded-xl p-5 border-l-4 border-l-[#f59e0b] shadow-sm">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-[#56423d] text-sm font-medium">⏳ Pre-order hôm nay</p>
                <p class="text-2xl md:text-3xl font-bold text-[#1b1c1a] mt-2">5.200.000₫</p>
                <p class="text-xs text-[#f59e0b] mt-2"><span class="material-symbols-outlined text-sm align-middle">schedule</span> 24 đơn chờ xác nhận</p>
              </div>
              <div class="w-12 h-12 rounded-full bg-[#f59e0b]/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-[#f59e0b] text-2xl">pending</span>
              </div>
            </div>
            <div class="mt-3 pt-2 border-t">
              <div class="flex justify-between text-xs text-[#89726c]"><span>🚚 Dự kiến giao: 15/06</span><span>📊 85% hoàn thành</span></div>
            </div>
          </div>
        </div>

        <!-- Quick stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white rounded-lg p-4 text-center border"><p class="text-2xl font-bold text-[#ff6b00]">156</p><p class="text-xs text-[#56423d]">Khách hàng mới</p></div>
          <div class="bg-white rounded-lg p-4 text-center border"><p class="text-2xl font-bold text-[#ff6b00]">284</p><p class="text-xs text-[#56423d]">Tổng đơn hàng</p></div>
          <div class="bg-white rounded-lg p-4 text-center border"><p class="text-2xl font-bold text-[#ff6b00]">98%</p><p class="text-xs text-[#56423d]">Hài lòng khách hàng</p></div>
          <div class="bg-white rounded-lg p-4 text-center border"><p class="text-2xl font-bold text-[#ff6b00]">12</p><p class="text-xs text-[#56423d]">Sản phẩm sắp hết</p></div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <div class="bg-white rounded-xl p-6 border shadow-sm">
            <div class="flex justify-between mb-4">
              <h3 class="font-semibold">Doanh thu theo loại hình</h3>
              <select class="text-sm border rounded-lg px-2 py-1"><option>7 ngày qua</option><option>Tháng này</option></select>
            </div>
            <canvas id="revenueByTypeChart" height="200"></canvas>
          </div>
          <div class="bg-white rounded-xl p-6 border shadow-sm">
            <h3 class="font-semibold mb-4">Đơn hàng gần đây</h3>
            <div class="space-y-3">
              <div v-for="order in recentOrders" class="flex justify-between items-center p-2 hover:bg-[#fff5f2] rounded-lg">
                <div><p class="text-sm font-medium">{{ order.code }}</p><p class="text-xs text-[#89726c]">{{ order.customer }} - {{ order.type }}</p></div>
                <span class="text-xs px-2 py-1 rounded-full" :class="order.statusClass">{{ order.status }}</span>
                <span class="text-sm font-semibold">{{ order.amount }}</span>
              </div>
            </div>
            <a href="#" class="block text-center mt-3 text-sm text-[#ff6b00]">Xem tất cả</a>
          </div>
        </div>

        <!-- Top products -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-xl p-5 border">
            <h3 class="font-semibold mb-3 flex items-center gap-2"><span class="text-[#ff6b00]">🛒</span> Top bán lẻ</h3>
            <div class="space-y-2">
              <div v-for="(p, idx) in topRetail" class="flex justify-between text-sm">
                <span>{{ idx+1 }}. {{ p.name }}</span><span class="font-semibold">{{ p.sold }} cái</span>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-xl p-5 border">
            <h3 class="font-semibold mb-3 flex items-center gap-2"><span class="text-[#436651]">🏭</span> Top bán sỉ</h3>
            <div class="space-y-2">
              <div v-for="(p, idx) in topWholesale" class="flex justify-between text-sm">
                <span>{{ idx+1 }}. {{ p.name }}</span><span class="font-semibold">{{ p.quantity }} đơn</span>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-xl p-5 border">
            <h3 class="font-semibold mb-3 flex items-center gap-2"><span class="text-[#f59e0b]">⏳</span> Pre-order nổi bật</h3>
            <div class="space-y-2">
              <div v-for="(p, idx) in topPreorder" class="flex justify-between text-sm">
                <span>{{ idx+1 }}. {{ p.name }}</span><span class="font-semibold">{{ p.preordered }} đơn</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.sidebar-item-active {
  background-color: #fff5f2;
  color: #ff6b00;
  border-right: 3px solid #ff6b00;
}
.glass-header {
  backdrop-filter: blur(12px);
  background-color: rgba(251, 249, 245, 0.95);
}
.stat-card {
  transition: all 0.2s;
  cursor: pointer;
}
.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
}
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: #e4e2de;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb {
  background: #ff6b00;
  border-radius: 10px;
}
</style>