<template>
    <ckeditor
        v-if="editor"
        :editor="editor"
        :model-value="content"
        :config="editorConfig"
        class="ck-editor-custom"
        @ready="onEditorReady"
        @input="onInput"
    />
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
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
let editorInstance = null;

const editorConfig = {
    toolbar: [
        'heading', '|',
        'bold', 'italic', 'underline', 'strikethrough', 'link', 'bulletedList', 'numberedList', '|',
        'blockQuote', '|',
        'undo', 'redo'
    ],
};

const onEditorReady = (editor) => {
    editorInstance = editor;
    if (content.value) {
        editor.setData(content.value);
    }
};

const onInput = (event, editor) => {
    const data = editor.getData();
    content.value = data;
    emit('update:modelValue', data);
};

// Đồng bộ khi modelValue thay đổi từ bên ngoài
watch(() => props.modelValue, (newVal) => {
    content.value = newVal;
    if (editorInstance) {
        editorInstance.setData(newVal);
    }
}, { immediate: true, flush: 'post' });

// Đảm bảo sau khi mount, nếu editor đã sẵn sàng nhưng content có giá trị, set data
onMounted(() => {
    nextTick(() => {
        if (editorInstance && content.value) {
            editorInstance.setData(content.value);
        }
    });
});
</script>

<style scoped>
.ck-editor-custom :deep(.ck-editor__editable) {
    min-height: 200px;
    max-height: 400px;
}
</style>