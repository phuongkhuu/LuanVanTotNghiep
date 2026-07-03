<template>
  <div ref="triggerRef" class="relative w-full">
    <!-- Nút hiển thị -->
    <div
      class="border rounded px-2 py-1 cursor-pointer flex items-center gap-2 bg-white"
      :class="{ 'border-red-500': error }"
      @click="toggleDropdown"
    >
      <div
        v-if="selectedColor"
        class="w-5 h-5 rounded border border-gray-300 flex-shrink-0"
        :style="{ backgroundColor: selectedColor.code || getColorCode(selectedColor.name) }"
      ></div>
      <span class="flex-1 truncate">{{ selectedColor ? selectedColor.name : placeholder }}</span>
      <span class="text-gray-400 text-sm">▼</span>
    </div>

    <!-- Dropdown được teleport ra body -->
    <Teleport to="body">
      <div
        v-if="isOpen"
        ref="floatingRef"
        class="z-[9999] min-w-[200px] bg-white border rounded shadow-lg max-h-60 overflow-auto"
        :style="floatingStyle"
      >
        <div
          v-for="color in colors"
          :key="color.id"
          class="px-2 py-1.5 hover:bg-gray-100 cursor-pointer flex items-center gap-2"
          @click="selectColor(color)"
        >
          <div
            class="w-5 h-5 rounded border border-gray-300 flex-shrink-0"
            :style="{ backgroundColor: color.code || getColorCode(color.name) }"
          ></div>
          <span>{{ color.name }}</span>
        </div>
        <div v-if="!colors.length" class="px-2 py-2 text-gray-400 text-sm">Không có màu nào</div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useFloating, offset, flip, shift, autoUpdate } from '@floating-ui/vue'

const props = defineProps({
  modelValue: { type: [Number, String], default: null },
  colors: { type: Array, default: () => [] },
  placeholder: { type: String, default: '-- Chọn màu --' },
  error: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const triggerRef = ref(null)
const floatingRef = ref(null)

// Dùng Floating UI để tính vị trí
const { x, y, strategy, update } = useFloating(triggerRef, floatingRef, {
  placement: 'bottom-start',
  middleware: [offset(4), flip(), shift({ padding: 8 })],
  whileElementsMounted: autoUpdate,
})

const floatingStyle = computed(() => ({
  position: strategy.value,
  top: y.value + 'px',
  left: x.value + 'px',
  width: triggerRef.value ? triggerRef.value.offsetWidth + 'px' : 'auto',
}))

const selectedColor = computed(() => {
  return props.colors.find(c => c.id === props.modelValue) || null
})

const getColorCode = (name) => {
  if (!name) return '#CCCCCC'
  if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(name)) return name.toUpperCase()
  const colorMap = {
    'đen': '#000000', 'den': '#000000', 'black': '#000000',
    'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
    'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
    'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
    'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
    'cam': '#FFA500', 'orange': '#FFA500',
    'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
    'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
    'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
    'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
    'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
    'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
    'be': '#F5F5DC', 'beige': '#F5F5DC',
    'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
    'tím than': '#490C42', 'tim than': '#490C42'
  }
  return colorMap[name.toLowerCase().trim()] || '#CCCCCC'
}

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    // Cập nhật vị trí khi mở
    nextTick(() => update())
  }
}

const selectColor = (color) => {
  emit('update:modelValue', color.id)
  isOpen.value = false
}

// Đóng khi click ra ngoài
const handleClickOutside = (event) => {
  if (!triggerRef.value?.contains(event.target) && !floatingRef.value?.contains(event.target)) {
    isOpen.value = false
  }
}

watch(isOpen, (newVal) => {
  if (newVal) {
    document.addEventListener('click', handleClickOutside)
  } else {
    document.removeEventListener('click', handleClickOutside)
  }
})
</script>