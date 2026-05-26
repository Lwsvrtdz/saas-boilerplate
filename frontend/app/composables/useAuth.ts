type Organization = {
  id: number
  name: string
  slug: string
  settings?: Record<string, unknown> | null
}

type AuthUser = {
  id: number
  name: string
  email: string
  currentOrganization: Organization | null
  organizations: Organization[]
}

type AuthResponse = {
  token: string
  user: AuthUser
  organization?: Organization
}

type MeResponse = {
  user: AuthUser | null
  organization: Organization | null
}

export const useAuth = () => {
  const config = useRuntimeConfig()
  const api = useApi()
  const token = useState<string | null>('auth-token', () => null)
  const user = useState<AuthUser | null>('auth-user', () => null)
  const currentOrganization = useState<Organization | null>('auth-organization', () => null)
  const organizations = useState<Organization[]>('auth-organizations', () => [])

  const applySession = (response: AuthResponse) => {
    token.value = response.token
    user.value = response.user
    currentOrganization.value = response.organization ?? response.user.currentOrganization
    organizations.value = response.user.organizations
  }

  const login = async (payload: { email: string; password: string; device_name?: string }) => {
    const response = await api.request<AuthResponse>('/auth/login', {
      method: 'POST',
      body: payload,
    })

    applySession(response)
    await navigateTo('/app')
  }

  const register = async (payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
    device_name?: string
  }) => {
    const response = await api.request<AuthResponse>('/auth/register', {
      method: 'POST',
      body: payload,
    })

    applySession(response)
    await navigateTo('/app')
  }

  const fetchMe = async () => {
    if (!token.value) {
      return
    }

    const response = await api.request<MeResponse>('/auth/me')
    user.value = response.user
    currentOrganization.value = response.organization
    organizations.value = response.user?.organizations ?? []
  }

  const fetchOrganizations = async () => {
    if (!token.value) {
      organizations.value = []
      return []
    }

    organizations.value = await api.request<Organization[]>('/me/organizations')
    return organizations.value
  }

  const switchOrganization = async (organization: Organization) => {
    const response = await api.request<Organization>('/auth/organizations/current', {
      method: 'PATCH',
      body: { organization_id: organization.id },
    })

    currentOrganization.value = response
    await fetchMe()
  }

  const logout = async () => {
    if (token.value) {
      await api.request('/auth/logout', { method: 'POST' }).catch(() => undefined)
    }

    token.value = null
    user.value = null
    currentOrganization.value = null
    organizations.value = []
    await navigateTo('/login')
  }

  return {
    appName: config.public.appName,
    token,
    user,
    currentOrganization,
    organizations,
    login,
    register,
    fetchMe,
    fetchOrganizations,
    switchOrganization,
    logout,
  }
}
