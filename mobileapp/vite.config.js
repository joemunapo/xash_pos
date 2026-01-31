import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import legacy from '@vitejs/plugin-legacy'
import { fileURLToPath, URL } from 'node:url'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),

    legacy({
      targets: ['Android >= 7'],
      modernPolyfills: true,
      additionalLegacyPolyfills: ['regenerator-runtime/runtime']
    })
  ],

  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },

  build: {
    cssTarget: 'chrome61',
    sourcemap: false,
    minify: 'terser'
  },

  esbuild: {
    target: 'es2015'
  },

})
