<template>
    <ckeditor
        v-if="editor"
        :editor="editor"
        v-model="content"
        :config="editorConfig"
    />
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const editor = ClassicEditor;
const content = ref(props.modelValue);

// Đồng bộ với prop modelValue
watch(() => props.modelValue, (newVal) => {
    content.value = newVal;
});

// Phát sự kiện khi nội dung thay đổi
watch(content, (newVal) => {
    emit('update:modelValue', newVal);
});

// Cấu hình editor (tùy chỉnh toolbar, plugins,...)
const editorConfig = {
    toolbar: [
        'heading', '|',
        'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
        'outdent', 'indent', '|',
        'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', '|',
        'undo', 'redo'
    ],
};
</script>