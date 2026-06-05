<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

// Menu items cho Web User
const navigation = [
    { name: 'Trang chủ', href: route('home'), icon: 'home' },
    { name: 'Sản phẩm', href: '#', icon: 'inventory_2' },
    { name: 'Mua sỉ', href: route('wholesale'), icon: 'business' },
    { name: 'Tùy chỉnh', href: route('customize'), icon: 'palette' },
    { name: 'Khuyến mãi', href: route('promotion'), icon: 'local_offer' },
];
</script>

<template>
    <div>
        <div class="min-h-screen bg-[#fbf9f5]">
            <!-- Header Navigation -->
            <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('home')">
                                <h1 class="text-2xl font-bold text-[#ff6b00]">
                                    BigBag<span class="text-gray-800">.vn</span>
                                </h1>
                            </Link>
                        </div>

                        <!-- Desktop Navigation Links -->
                        <div class="hidden space-x-8 sm:flex sm:items-center">
                            <Link 
                                v-for="item in navigation"
                                :key="item.name"
                                :href="item.href"
                                class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-600 hover:text-[#ff6b00] transition-colors"
                                :class="{ 'text-[#ff6b00] border-b-2 border-[#ff6b00]': $page.url === item.href }"
                            >
                                <span class="material-symbols-outlined text-lg mr-1">{{ item.icon }}</span>
                                {{ item.name }}
                            </Link>
                        </div>

                        <!-- User Menu (Desktop) -->
                        <div class="hidden sm:flex sm:items-center sm:space-x-4">
                            <!-- Cart Icon -->
                            <Link :href="route('cart')" class="relative text-gray-600 hover:text-[#ff6b00] transition-colors">
                                <span class="material-symbols-outlined">shopping_cart</span>
                                <span class="absolute -top-1 -right-2 w-4 h-4 bg-[#ff6b00] text-white text-[9px] rounded-full flex items-center justify-center">0</span>
                            </Link>

                            <!-- User Dropdown -->
                            <div class="relative ml-3">
                                <div class="relative">
                                    <button
                                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                                        class="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors focus:outline-none"
                                    >
                                        <div class="w-6 h-6 rounded-full bg-[#ff6b00] flex items-center justify-center text-white text-xs font-bold">
                                            {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        {{ $page.props.auth.user.name }}
                                        <svg
                                            class="h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div
                                        v-if="showingNavigationDropdown"
                                        class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                    >
                                        <Link
                                            :href="route('profile.edit')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            <span class="material-symbols-outlined text-sm align-middle mr-2">person</span>
                                            Tài khoản của tôi
                                        </Link>
                                        <Link
                                            v-if="$page.props.auth.user.role === 'admin'"
                                            :href="route('admin.dashboard')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            <span class="material-symbols-outlined text-sm align-middle mr-2">admin_panel_settings</span>
                                            Trang quản trị
                                        </Link>
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                        >
                                            <span class="material-symbols-outlined text-sm align-middle mr-2">logout</span>
                                            Đăng xuất
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Menu Button -->
                        <div class="flex items-center gap-2 sm:hidden">
                            <Link :href="route('cart')" class="relative text-gray-600 hover:text-[#ff6b00]">
                                <span class="material-symbols-outlined">shopping_cart</span>
                                <span class="absolute -top-1 -right-2 w-4 h-4 bg-[#ff6b00] text-white text-[9px] rounded-full flex items-center justify-center">0</span>
                            </Link>
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            class="block px-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-100 hover:text-[#ff6b00]"
                            :class="{ 'bg-orange-50 text-[#ff6b00]': $page.url === item.href }"
                        >
                            <span class="material-symbols-outlined text-lg align-middle mr-2">{{ item.icon }}</span>
                            {{ item.name }}
                        </Link>
                    </div>

                    <!-- Mobile User Menu -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-[#ff6b00] flex items-center justify-center text-white text-sm font-bold">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <Link :href="route('profile.edit')" class="block px-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-100">
                                <span class="material-symbols-outlined text-lg align-middle mr-2">person</span>
                                Tài khoản của tôi
                            </Link>
                            <Link
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('admin.dashboard')"
                                class="block px-4 py-2 text-base font-medium text-gray-600 hover:bg-gray-100"
                            >
                                <span class="material-symbols-outlined text-lg align-middle mr-2">admin_panel_settings</span>
                                Trang quản trị
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full text-left px-4 py-2 text-base font-medium text-red-600 hover:bg-gray-100"
                            >
                                <span class="material-symbols-outlined text-lg align-middle mr-2">logout</span>
                                Đăng xuất
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow-sm" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>