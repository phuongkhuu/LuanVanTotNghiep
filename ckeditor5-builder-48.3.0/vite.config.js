import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    build: {
        lib: {
            entry: 'main.js',
            name: 'CKEditor5Custom',
            fileName: (format) => `ckeditor.${format}.js`
        },
        rollupOptions: {
            external: ['vue', 'ckeditor5'],
            output: {
                globals: {
                    vue: 'Vue',
                    ckeditor5: 'CKEditor5'
                }
            }
        }
    }
});