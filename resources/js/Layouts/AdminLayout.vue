<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Sidebar state
const sidebarCollapsed = ref(false);

// Submenu states - mở rộng dựa trên route hiện tại
const orderSubmenuOpen = ref(false);
const productSubmenuOpen = ref(false);
const customerSubmenuOpen = ref(false);
const attributeSubmenuOpen = ref(false);

// Lấy thông tin user từ page props
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Kiểm tra route hiện tại
const currentRoute = computed(() => page.component);

// Tự động mở submenu dựa trên route hiện tại
const checkActiveSubmenus = () => {
    const route = currentRoute.value;
    
    // Kiểm tra submenu Đơn hàng
    if (route === 'Admin/Orders' || route?.includes('Orders')) {
        orderSubmenuOpen.value = true;
    }
    
    // Kiểm tra submenu Sản phẩm
    if (route === 'Admin/Products' || route?.includes('Products')) {
        productSubmenuOpen.value = true;
    }
    
    // Kiểm tra submenu Khách hàng
    if (route === 'Admin/Customers' || route?.includes('Customers')) {
        customerSubmenuOpen.value = true;
    }
    
    // Kiểm tra submenu Thuộc tính
    if (route === 'Admin/Categories' || route === 'Admin/Colors' || route === 'Admin/Brands' || 
        route?.includes('Categories') || route?.includes('Colors') || route?.includes('Brands')) {
        attributeSubmenuOpen.value = true;
    }
};

// Gọi kiểm tra khi component mount
setTimeout(checkActiveSubmenus, 100);

// Toggle functions
const toggleOrderSubmenu = () => {
    orderSubmenuOpen.value = !orderSubmenuOpen.value;
};
const toggleProductSubmenu = () => {
    productSubmenuOpen.value = !productSubmenuOpen.value;
};
const toggleCustomerSubmenu = () => {
    customerSubmenuOpen.value = !customerSubmenuOpen.value;
};
const toggleAttributeSubmenu = () => {
    attributeSubmenuOpen.value = !attributeSubmenuOpen.value;
};

// Kiểm tra active cho menu item
const isActive = (routeNames) => {
    if (typeof routeNames === 'string') {
        return route().current(routeNames);
    }
    if (Array.isArray(routeNames)) {
        return routeNames.some(name => route().current(name));
    }
    return false;
};
</script>

<template>
    <div class="flex min-h-screen bg-background">
        <!-- ==================== SIDEBAR ==================== -->
        <aside 
            class="w-72 bg-white border-r border-border-light fixed h-full z-20 transition-all duration-300 flex flex-col"
            :class="{ 'hidden lg:block': sidebarCollapsed, 'block': !sidebarCollapsed }"
        >
            <!-- Logo - Phần cố định đầu trang -->
            <div class="p-6 border-b border-border-light flex-shrink-0">
                <h1 class="text-2xl font-bold text-primary">BigBag<span class="text-on-surface">.vn</span></h1>
                <p class="text-xs text-on-surface-variant mt-1">Admin Portal</p>
            </div>
            
            <!-- Menu Area - Có thể cuộn -->
            <div class="flex-1 overflow-y-auto">
                <nav class="p-4 space-y-1">
                    <!-- Dashboard -->
                    <Link :href="route('admin.dashboard')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.dashboard') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="flex-1 text-sm font-medium">Dashboard</span>
                    </Link>
                    
                    <!-- Đơn hàng -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleOrderSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.orders.index', 'admin.orders.show']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">receipt_long</span>
                            <span class="flex-1 text-sm font-medium">Đơn hàng</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': orderSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="orderSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.orders.index', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'retail' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                🛒 Đơn bán lẻ
                            </Link>
                            <Link :href="route('admin.orders.index', { type: 'wholesale' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'wholesale' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                🏭 Đơn bán sỉ
                            </Link>
                            <Link :href="route('admin.orders.index', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'preorder' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                ⏳ Đơn Pre-order
                            </Link>
                        </div>
                    </div>

                    <!-- Sản phẩm -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleProductSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.products.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">inventory_2</span>
                            <span class="flex-1 text-sm font-medium">Sản phẩm</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': productSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="productSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.products.index', { type: 'normal' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.products.index') && route().params?.type === 'normal' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                🎒 Sản phẩm thường
                            </Link>
                            <Link :href="route('admin.products.index', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.products.index') && route().params?.type === 'preorder' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                🔮 Sản phẩm Pre-order
                            </Link>
                        </div>
                    </div>

                    <!-- Thuộc tính sản phẩm -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleAttributeSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.categories.index', 'admin.colors.index', 'admin.brands.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">settings_input_component</span>
                            <span class="flex-1 text-sm font-medium">Thuộc tính</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': attributeSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="attributeSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.categories.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.categories.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                📁 Danh mục
                            </Link>
                            <Link :href="route('admin.colors.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.colors.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                🎨 Màu sắc
                            </Link>
                            <Link :href="route('admin.brands.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.brands.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                🏷️ Thương hiệu
                            </Link>
                        </div>
                    </div>

                    <!-- Khách hàng -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleCustomerSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.customers.index', 'admin.customers.retail', 'admin.customers.business']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">group</span>
                            <span class="flex-1 text-sm font-medium">Khách hàng</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': customerSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="customerSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.customers.index', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.customers.index') && route().params?.type === 'retail' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                👤 Khách hàng lẻ
                            </Link>
                            <Link :href="route('admin.customers.index', { type: 'business' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.customers.index') && route().params?.type === 'business' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                                🏢 Khách hàng doanh nghiệp
                            </Link>
                        </div>
                    </div>

                    <!-- Tùy chỉnh -->
                    <Link :href="route('admin.customize.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.customize.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">palette</span>
                        <span class="flex-1 text-sm font-medium">Tùy chỉnh</span>
                        <span class="text-xs bg-primary text-white px-2 py-0.5 rounded-full">3 mới</span>
                    </Link>

                    <!-- Khuyến mãi -->
                    <Link :href="route('admin.promotions.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.promotions.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">local_offer</span>
                        <span class="flex-1 text-sm font-medium">Khuyến mãi</span>
                    </Link>

                    <!-- Báo cáo -->
                    <Link :href="route('admin.reports.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.reports.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">bar_chart</span>
                        <span class="flex-1 text-sm font-medium">Báo cáo</span>
                    </Link>

                    <!-- Cài đặt -->
                    <Link :href="route('admin.settings.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.settings.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">settings</span>
                        <span class="flex-1 text-sm font-medium">Cài đặt</span>
                    </Link>

                    <!-- Liên kết về trang chủ -->
                    <Link :href="route('home')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-on-surface-variant hover:bg-hover-bg hover:text-primary">
                        <span class="material-symbols-outlined">home</span>
                        <span class="flex-1 text-sm font-medium">Về trang chủ</span>
                    </Link>
                </nav>
            </div>
            
            <!-- Admin Info - Cố định ở dưới cùng -->
            <div class="flex-shrink-0 p-4 border-t border-border-light bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-on-surface truncate">{{ user?.name || 'Admin User' }}</p>
                        <p class="text-xs text-outline truncate">{{ user?.email || 'admin@bigbag.vn' }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-primary transition-colors flex-shrink-0">
                        logout
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Mobile overlay -->
        <div 
            v-if="!sidebarCollapsed" 
            @click="sidebarCollapsed = true" 
            class="fixed inset-0 bg-black/50 z-10 lg:hidden"
        ></div>

        <!-- ==================== MAIN CONTENT ==================== -->
        <main class="flex-1 lg:ml-72 transition-all duration-300">
            <!-- Header -->
            <header class="sticky top-0 z-10 glass-header border-b border-border-light px-4 md:px-8 py-4 flex justify-between items-center bg-white/95 backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-on-surface-variant hover:text-primary lg:hidden">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="relative hidden md:block">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
                        <input 
                            type="text" 
                            placeholder="Tìm kiếm..." 
                            class="pl-10 pr-4 py-2 bg-white border border-border-light rounded-full w-80 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
                        >
                    </div>
                </div>
                <div class="flex items-center gap-4">               
                    <div class="flex items-center gap-2 pl-2 border-l border-border-light">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm">
                            {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                        </div>
                        <span class="text-sm font-medium text-on-surface hidden md:block">{{ user?.name || 'Admin' }}</span>
                    </div>
                </div>
            </header>

            <!-- Slot cho nội dung chính -->
            <slot />
        </main>
    </div>
</template>

<style scoped>
.sidebar-item-active {
    background-color: #fff5f2;
    color: #ff6b00;
}

.rotate-180 {
    transform: rotate(180deg);
}

.glass-header {
    backdrop-filter: blur(12px);
    background-color: rgba(251, 249, 245, 0.95);
}

/* Custom scrollbar cho phần menu */
aside .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

aside .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

aside .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

aside .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #ff6b00;
}
</style>