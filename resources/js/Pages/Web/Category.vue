<template>
  <div>
    <Head title="Danh mục sản phẩm - BigBag Premium Utility Carry Gear" />
    <!-- Header -->
    <main class="pt-8 pb-section-gap">
      <section class="px-margin-desktop max-w-[1440px] mx-auto mb-stack-lg">
        <div class="py-stack-lg border-b border-outline-variant">
          <nav class="flex items-center text-on-surface-variant mb-stack-sm space-x-2"><Link href="/" class="font-label-md hover:text-primary">Trang chủ</Link><span class="material-symbols-outlined text-[14px]">chevron_right</span><span class="font-label-md text-on-surface">Dòng sản phẩm Du lịch</span></nav>
          <h1 class="font-display-lg text-display-lg text-on-surface mb-stack-sm">Dòng Sản Phẩm Du Lịch</h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">Khám phá bộ sưu tập túi du lịch cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      <section class="px-margin-desktop max-w-[1440px] mx-auto flex flex-col md:flex-row gap-gutter">
        <aside class="w-full md:w-64 flex-shrink-0 space-y-stack-lg">
          <div><h3 class="font-headline-sm mb-stack-md">Phân loại</h3><ul><li v-for="cat in filterCategories" :key="cat.key" class="flex items-center mb-2"><input type="checkbox" :id="cat.key" class="rounded border-outline text-primary h-4 w-4"><label :for="cat.key" class="ml-2">{{ cat.label }}</label></li></ul></div>
          <div><h3 class="font-headline-sm mb-stack-md">Thương hiệu</h3><ul><li v-for="brand in filterBrands" :key="brand.key" class="flex items-center mb-2"><input type="checkbox" :id="brand.key" class="rounded border-outline text-primary h-4 w-4"><label :for="brand.key" class="ml-2">{{ brand.label }}</label></li></ul></div>
          <div><h3 class="font-headline-sm mb-stack-md">Chất liệu</h3><ul><li v-for="mat in filterMaterials" :key="mat.key" class="flex items-center mb-2"><input type="checkbox" :id="mat.key" class="rounded border-outline text-primary h-4 w-4"><label :for="mat.key" class="ml-2">{{ mat.label }}</label></li></ul></div>
          <div><h3 class="font-headline-sm mb-stack-md">Màu sắc</h3><div class="flex flex-wrap gap-2"><button v-for="color in filterColors" :key="color.code" class="w-6 h-6 rounded-full border" :style="{ backgroundColor: color.code }" :title="color.label"></button></div></div>
          <div><h3 class="font-headline-sm mb-stack-md">Khoảng giá</h3><input type="range" class="w-full h-1 bg-surface-container-highest rounded-lg accent-primary"><div class="flex justify-between mt-1 text-sm"><span>1.000.000₫</span><span>10.000.000₫</span></div></div>
          <button class="w-full py-3 px-6 bg-primary text-white rounded-lg">Áp dụng lọc</button>
        </aside>
        <div class="flex-grow">
          <div class="flex justify-between items-center mb-stack-lg"><span class="text-sm">Hiển thị {{ products.length }} trên {{ products.length }} sản phẩm</span><div class="flex items-center gap-2"><span>Sắp xếp:</span><select class="border-none bg-transparent"><option v-for="opt in sortOptions" :value="opt.value">{{ opt.label }}</option></select></div></div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-gutter">
            <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-warm rounded-lg overflow-hidden flex flex-col">
              <div class="relative aspect-[4/5] bg-surface-container-low overflow-hidden"><img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform"><span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">{{ product.badge }}</span><button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100"><span class="material-symbols-outlined">favorite</span></button></div>
              <div class="p-stack-md flex flex-col flex-grow"><p class="text-xs text-on-surface-variant uppercase tracking-wider mb-1">{{ product.brandCategory }}</p><h3 class="font-headline-sm text-[16px] mb-1">{{ product.name }}</h3><div class="flex items-baseline space-x-2 mt-auto"><span class="font-headline-md text-headline-md text-primary">{{ product.price }}</span><span v-if="product.oldPrice" class="text-sm line-through text-on-surface-variant/60">{{ product.oldPrice }}</span></div><button class="w-full mt-4 py-3 bg-primary text-white rounded-xl font-bold">Thêm vào giỏ hàng</button></div>
            </div>
          </div>
          <div class="mt-section-gap flex justify-center space-x-2"><button class="w-10 h-10 rounded border"><span class="material-symbols-outlined">chevron_left</span></button><button class="w-10 h-10 rounded bg-primary text-white">1</button><button class="w-10 h-10 rounded border">2</button><button class="w-10 h-10 rounded border">3</button><span class="px-2">...</span><button class="w-10 h-10 rounded border">8</button><button class="w-10 h-10 rounded border"><span class="material-symbols-outlined">chevron_right</span></button></div>
        </div>
      </section>
    </main>
    <!-- Footer + Chatbot -->
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

const filterCategories = ref([{ key: 'cat_luggage', label: 'Balo hành lý' }, { key: 'cat_duffle', label: 'Túi Duffle' }, { key: 'cat_carryon', label: 'Vali xách tay' }])
const filterBrands = ref([{ key: 'brand_apex', label: 'Apex' }, { key: 'brand_nomad', label: 'Nomad' }, { key: 'brand_orbit', label: 'Orbit' }])
const filterMaterials = ref([{ key: 'mat_nylon', label: 'Ballistic Nylon' }, { key: 'mat_leather', label: 'Da cao cấp' }, { key: 'mat_tpu', label: 'Chống nước (TPU)' }])
const filterColors = ref([{ code: '#000000', label: 'Đen' }, { code: '#1e3a8a', label: 'Xanh navy' }, { code: '#9ca3af', label: 'Xám' }, { code: '#78350f', label: 'Nâu' }])
const sortOptions = ref([{ value: 'newest', label: 'Mới nhất' }, { value: 'price_asc', label: 'Giá: Thấp đến Cao' }, { value: 'price_desc', label: 'Giá: Cao đến Thấp' }, { value: 'popular', label: 'Phổ biến nhất' }])

const products = ref([ /* ... 6 sản phẩm mẫu như trong file gốc ... */ ])
</script>