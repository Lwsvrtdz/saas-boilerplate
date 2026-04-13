type AuthUser = {
  id: number
  name: string
  email: string
  currentOrganization: { id: number; name: string; slug: string } | null
  organizations: Array<{ id: number; name: string; slug: string }>
}

type AuthResponse = {
  data: {
    token: string
    user: AuthUser
  }
}

type MeResponse = {
  data: {
    user: AuthUser | null
    organization: { id: number; name: string; slug: string } | null
  }
}

export const useAuth = () => {
  const config = useRuntimeConfig()
  const api = useApi()
  const token = useState<string | null>('auth-token', () => null)
  const user = useState<AuthUser | null>('auth-user', () => null)
  const currentOrganization = useState<MeResponse['data']['organization']>('auth-organization', () => null)

  const login = async (payload: { email: string; password: string; device_name?: string }) => {
    const response = await api.request<AuthResponse>('/auth/login', {
      method: 'POST',
      body: payload,
    })

    token.value = response.data.token
    user.value = response.data.user
    currentOrganization.value = response.data.user.currentOrganization

    await navigateTo('/app')
  }

  const fetchMe = async () => {
    if (!token.value) {
      return
    }

    const response = await api.request<MeResponse>('/auth/me')
    user.value = response.data.user
    currentOrganization.value = response.data.organization
  }

  const logout = async () => {
    if (token.value) {
      await api.request('/auth/logout', { method: 'POST' }).catch(() => undefined)
    }

    token.value = null
    user.value = null
    currentOrganization.value = null
    await navigateTo('/login')
  }

  return {
    appName: config.public.appName,
    token,
    user,
    currentOrganization,
    login,
    fetchMe,
    logout,
  }
}
