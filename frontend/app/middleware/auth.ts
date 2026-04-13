export default defineNuxtRouteMiddleware(async () => {
  const auth = useAuth()

  if (!auth.token.value) {
    return navigateTo('/login')
  }

  if (!auth.user.value) {
    await auth.fetchMe().catch(async () => {
      await auth.logout()
    })
  }
})
