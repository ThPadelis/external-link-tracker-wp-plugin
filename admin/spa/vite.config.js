import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  base: './',
  build: {
    outDir: '../dist',
    emptyOutDir: true,
    assetsDir: '',
    rollupOptions: {
      output: {
        entryFileNames: 'elt-admin.js',
        chunkFileNames: 'elt-admin-[name].js',
        assetFileNames: 'elt-admin.[ext]',
        banner: '/* Admin UI source: admin/spa/ (Vue + Vite). Build with npm ci && npm run build. */',
      },
    },
  },
})
