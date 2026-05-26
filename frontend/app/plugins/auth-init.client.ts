export default defineNuxtPlugin(async () => {
  const auth = useAuth()
  const storageKey = 'saas-boilerplate.token'

  if (!auth.token.value) {
    auth.token.value = window.localStorage.getItem(storageKey)
  }

  if (auth.token.value && !auth.user.value) {
    await auth.fetchMe().catch(() => {
      auth.token.value = null
    })
  }
})
