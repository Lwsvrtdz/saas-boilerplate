export default defineNuxtPlugin(() => {
  const auth = useAuth()

  const storageKey = 'saas-boilerplate.token'

  if (import.meta.client) {
    const storedToken = window.localStorage.getItem(storageKey)

    if (storedToken && !auth.token.value) {
      auth.token.value = storedToken
    }

    watch(
      auth.token,
      (value) => {
        if (value) {
          window.localStorage.setItem(storageKey, value)
          return
        }

        window.localStorage.removeItem(storageKey)
      },
      { immediate: true }
    )
  }
})
