export default defineNuxtConfig({
  srcDir: 'app',
  devtools: { enabled: true },
  css: ['~/assets/css/main.css'],
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api',
      appName: process.env.NUXT_PUBLIC_APP_NAME ?? 'SaaS Boilerplate',
    },
  },
  app: {
    head: {
      title: 'SaaS Boilerplate',
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Reusable Nuxt frontend foundation for a Laravel SaaS boilerplate.' },
      ],
    },
  },
})
