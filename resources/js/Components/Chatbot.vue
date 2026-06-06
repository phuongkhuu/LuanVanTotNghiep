<template>
  <div class="fixed bottom-8 right-8 z-50">
    <button class="w-16 h-16 rounded-full shadow-lg hover:scale-110 transition-transform flex items-center justify-center relative group bg-primary text-white hover:bg-primary-dark">
      <span class="material-symbols-outlined text-3xl">chat</span>
      <div class="absolute bottom-full right-0 mb-4 bg-white p-4 rounded-xl shadow-xl w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all pointer-events-none">
        <p class="font-label-lg text-label-lg text-gray-800 mb-1">Xin chào!</p>
        <p class="font-body-sm text-body-sm text-gray-600">Chúng tôi có thể giúp gì cho bạn hôm nay?</p>
        <div class="absolute bottom-[-8px] right-6 w-4 h-4 bg-white rotate-45"></div>
      </div>
    </button>
  </div>
</template>