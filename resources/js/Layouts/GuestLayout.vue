<script setup>
import { Link } from '@inertiajs/vue3';

// Menu items cho Gues
</script>

<template>
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

                    <!-- Auth Buttons -->
                    <div class="hidden sm:flex sm:items-center sm:space-x-4">
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-gray-600 hover:text-[#ff6b00] transition-colors"
                        >
                            Đăng nhập
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-lg bg-[#ff6b00] px-4 py-2 text-sm font-medium text-white hover:bg-orange-700 transition-colors"
                        >
                            Đăng ký
                        </Link>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center gap-2 sm:hidden">
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-[#ff6b00]"
                        >
                            Đăng nhập
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>