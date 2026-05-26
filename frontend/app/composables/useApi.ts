export const useApi = () => {
  const config = useRuntimeConfig()
  const token = useState<string | null>('auth-token', () => null)
  const currentOrganization = useState<{ id: number; name: string; slug: string } | null>('auth-organization', () => null)

  const request = async <T>(path: string, options: Parameters<typeof $fetch<T>>[1] = {}) => {
    const headers = {
      Accept: 'application/json',
      ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
      ...(currentOrganization.value ? { 'X-Organization': currentOrganization.value.slug } : {}),
      ...(options.headers ?? {}),
    }

    return await $fetch<T>(path, {
      baseURL: config.public.apiBaseUrl,
      ...options,
      headers,
    })
  }

  return { request }
}
