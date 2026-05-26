<script setup lang="ts">
definePageMeta({ middleware: ['guest'] })

const auth = useAuth()
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  device_name: 'nuxt-browser',
})
const error = ref('')

const submit = async () => {
  error.value = ''

  try {
    await auth.register(form)
  } catch (err) {
    error.value = 'Unable to create an account with the provided details.'
    console.error(err)
  }
}
</script>

<template>
  <AuthCard>
    <div class="stack">
      <div>
        <h1>Create account</h1>
        <p class="muted">Start with your first organization.</p>
      </div>

      <form class="stack" @submit.prevent="submit">
        <label class="field">
          <span>Name</span>
          <input v-model="form.name" type="text" autocomplete="name" placeholder="Taylor Otwell">
        </label>

        <label class="field">
          <span>Email</span>
          <input v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com">
        </label>

        <label class="field">
          <span>Password</span>
          <input v-model="form.password" type="password" autocomplete="new-password" placeholder="Minimum 8 characters">
        </label>

        <label class="field">
          <span>Confirm password</span>
          <input v-model="form.password_confirmation" type="password" autocomplete="new-password" placeholder="Repeat password">
        </label>

        <p v-if="error" class="error">{{ error }}</p>

        <button class="button" type="submit">Create account</button>
      </form>

      <p class="muted auth-link">
        Already have an account?
        <NuxtLink to="/login">Sign in</NuxtLink>
      </p>
    </div>
  </AuthCard>
</template>
