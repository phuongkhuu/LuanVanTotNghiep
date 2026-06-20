<script setup>
import { Head } from '@inertiajs/vue3';
import AppHeader from '@/Components/AppHeader.vue';
import AppFooter from '@/Components/AppFooter.vue';
</script>

<template>
    <div>
        <Head title="Đặt hàng thành công - BigBag" />
        <AppHeader />

        <main class="max-w-3xl mx-auto px-4 py-16 text-center">
            <div class="bg-green-100 text-green-700 p-4 rounded-full w-20 h-20 mx-auto flex items-center justify-center text-4xl">✓</div>
            <h1 class="text-3xl font-bold text-gray-800 mt-4">Đặt hàng thành công!</h1>
            <p class="text-gray-600 mt-2">Cảm ơn bạn đã mua hàng. Chúng tôi sẽ xử lý đơn hàng sớm nhất.</p>
            <a :href="route('home')" class="inline-block mt-6 bg-orange-600 text-white px-6 py-3 rounded-lg hover:bg-orange-700 transition">Tiếp tục mua sắm</a>
        </main>

       
    </div>
</template>