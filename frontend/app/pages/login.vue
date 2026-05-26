<script setup lang="ts">
definePageMeta({ middleware: ['guest'] })

const auth = useAuth()
const form = reactive({
  email: '',
  password: '',
  device_name: 'nuxt-browser',
})
const error = ref('')

const submit = async () => {
  error.value = ''

  try {
    await auth.login(form)
  } catch (err) {
    error.value = 'Unable to sign in with the provided credentials.'
    console.error(err)
  }
}
</script>

<template>
  <AuthCard>
    <div class="stack">
      <div>
        <h1>Sign in</h1>
        <p class="muted">Access your workspace.</p>
      </div>

      <form class="stack" @submit.prevent="submit">
        <label class="field">
          <span>Email</span>
          <input v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com">
        </label>

        <label class="field">
          <span>Password</span>
          <input v-model="form.password" type="password" autocomplete="current-password" placeholder="••••••••">
        </label>

        <p v-if="error" style="color:var(--danger);margin:0;">{{ error }}</p>

        <button class="button" type="submit">Continue</button>
      </form>

      <p class="muted auth-link">
        Need an account?
        <NuxtLink to="/register">Create one</NuxtLink>
      </p>
    </div>
  </AuthCard>
</template>
