import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'

export function useReportsQuery({ config, view, from, to, page, perPage, orderby, order }) {
  const queryKey = computed(() => [
    'elt-reports',
    view.value,
    from.value,
    to.value,
    page.value,
    perPage?.value ?? 20,
    orderby?.value ?? '',
    order?.value ?? 'desc',
  ])

  const query = useQuery({
    queryKey,
    queryFn: async () => {
      if (!config.restBase || !config.nonce) {
        throw new Error('Missing WordPress admin config for SPA.')
      }

      const endpoint = view.value === 'links' ? 'reports/links' : 'reports/domains'
      const params = new URLSearchParams({
        from: from.value,
        to: to.value,
        page: String(page.value),
        per_page: String(perPage?.value ?? 20),
      })
      if (orderby?.value) params.set('orderby', orderby.value)
      if (order?.value) params.set('order', order.value)

      const response = await fetch(`${config.restBase}${endpoint}?${params.toString()}`, {
        headers: {
          'X-WP-Nonce': config.nonce,
        },
        credentials: 'same-origin',
      })

      const payload = await response.json()
      if (!response.ok) {
        throw new Error(payload?.message || 'Failed to load report data.')
      }

      return {
        items: Array.isArray(payload.items) ? payload.items : [],
        total: Number(payload.total || 0),
        perPage: Number(payload.per_page || 20),
      }
    },
    staleTime: 30 * 1000,
    placeholderData: (previousData) => previousData,
  })

  const items = computed(() => query.data.value?.items || [])
  const total = computed(() => query.data.value?.total || 0)
  const loading = computed(() => query.isFetching.value)
  const error = computed(() => query.error.value?.message || '')

  return {
    items,
    total,
    loading,
    error,
  }
}
