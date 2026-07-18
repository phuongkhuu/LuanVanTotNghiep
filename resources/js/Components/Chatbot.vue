<template>
  <div class="fixed bottom-8 right-8 z-50">
    <!-- Nút mở/đóng chat -->
    <button
      @click="toggleChat"
      class="w-16 h-16 rounded-full shadow-lg hover:scale-110 transition-transform flex items-center justify-center bg-primary text-white hover:bg-primary-dark"
      :class="{ 'bg-primary-dark': isOpen }"
    >
      <span class="material-symbols-outlined text-3xl">
        {{ isOpen ? 'close' : 'chat' }}
      </span>
    </button>

    <!-- Cửa sổ chat -->
    <div
      v-if="isOpen"
      class="absolute bottom-20 right-0 w-96 h-[500px] bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden border border-gray-200"
    >
      <!-- Header -->
      <div class="bg-primary text-white px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined">support_agent</span>
          <span class="font-semibold">Trợ lý BigBag</span>
        </div>
        <button @click="isOpen = false" class="text-white hover:text-gray-200">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Khu vực tin nhắn -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50"
      >
        <div
          v-for="(msg, index) in messages"
          :key="index"
          class="flex"
          :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-[80%] rounded-lg px-4 py-2 text-sm shadow-sm"
            :class="
              msg.sender === 'user'
                ? 'bg-primary text-white rounded-br-none'
                : 'bg-white text-gray-800 rounded-bl-none'
            "
          >
            <div v-html="formatMessage(msg.text)"></div>
            <div class="text-xs mt-1 opacity-70">
              {{ formatTime(msg.timestamp) }}
            </div>
          </div>
        </div>

        <!-- Typing indicator -->
        <div v-if="isTyping" class="flex justify-start">
          <div class="bg-white rounded-lg px-4 py-2 shadow-sm flex items-center gap-1">
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
          </div>
        </div>
      </div>

      <!-- Input area -->
      <div class="border-t p-3 bg-white flex items-end gap-2">
        <textarea
          v-model="inputMessage"
          @keydown.enter.prevent="sendMessage"
          rows="1"
          placeholder="Nhập câu hỏi..."
          class="flex-1 resize-none border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
          :disabled="isLoading"
        ></textarea>
        <button
          @click="sendMessage"
          :disabled="isLoading || !inputMessage.trim()"
          class="bg-primary text-white rounded-lg px-4 py-2 hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          <span class="material-symbols-outlined text-xl">send</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ChatWidget',
  data() {
    return {
      isOpen: false,
      messages: [],
      inputMessage: '',
      isLoading: false,
      isTyping: false,
    };
  },
  mounted() {
    // Tải lịch sử chat từ localStorage nếu có
    const saved = localStorage.getItem('chat_messages');
    if (saved) {
      try {
        this.messages = JSON.parse(saved);
      } catch (e) {
        this.messages = [];
      }
    }
  },
  watch: {
    messages: {
      deep: true,
      handler(newVal) {
        localStorage.setItem('chat_messages', JSON.stringify(newVal));
        this.scrollToBottom();
      },
    },
  },
  methods: {
    toggleChat() {
      this.isOpen = !this.isOpen;
      if (this.isOpen) {
        this.$nextTick(() => this.scrollToBottom());
      }
    },

    async sendMessage() {
      const text = this.inputMessage.trim();
      if (!text || this.isLoading) return;

      // Thêm tin nhắn người dùng
      this.messages.push({
        sender: 'user',
        text: text,
        timestamp: new Date().toISOString(),
      });
      this.inputMessage = '';
      this.isLoading = true;
      this.isTyping = true;

      try {
        const response = await axios.post('/chat', { message: text });
        const reply = response.data.reply || 'Xin lỗi, tôi chưa hiểu câu hỏi.';

        // Thêm tin nhắn bot
        this.messages.push({
          sender: 'bot',
          text: reply,
          timestamp: new Date().toISOString(),
        });
      } catch (error) {
        console.error('Chat error:', error);
        this.messages.push({
          sender: 'bot',
          text: 'Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.',
          timestamp: new Date().toISOString(),
        });
      } finally {
        this.isLoading = false;
        this.isTyping = false;
      }
    },

    scrollToBottom() {
      const container = this.$refs.messagesContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    },

    formatMessage(text) {
      // Xử lý xuống dòng, link, v.v.
      return text.replace(/\n/g, '<br>');
    },

    formatTime(isoString) {
      const date = new Date(isoString);
      return date.toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
      });
    },
  },
};
</script>

<style scoped>
.animate-bounce {
  animation: bounce 1.2s infinite;
}
@keyframes bounce {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-8px); }
}
</style>