<script setup lang="ts">
definePageMeta({ middleware: [] })

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
        <div class="muted" style="text-transform:uppercase;letter-spacing:0.18em;font-size:0.8rem;">Authentication</div>
        <h1 style="margin:0.3rem 0 0;">Sign in</h1>
        <p class="muted">Swap this screen for your product-specific onboarding, SSO, invites, or registration flow.</p>
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
    </div>
  </AuthCard>
</template>
