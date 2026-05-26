export default defineNuxtRouteMiddleware(() => {
  const auth = useAuth()

  if (auth.token.value) {
    return navigateTo('/app')
  }
})
