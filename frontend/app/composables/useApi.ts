export const useApi = () => {
  const config = useRuntimeConfig()
  const token = useState<string | null>('auth-token', () => null)

  const request = async <T>(path: string, options: Parameters<typeof $fetch<T>>[1] = {}) => {
    const headers = {
      Accept: 'application/json',
      ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
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
