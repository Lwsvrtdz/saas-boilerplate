<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: ['auth', 'admin'],
})

const { request } = useApi()

const { data } = await useAsyncData('admin-overview', () =>
  request<{ metrics: { users: number; organizations: number; roles: number } }>('/admin/overview')
)
</script>

<template>
  <section class="stack">
    <div>
      <h1>Admin</h1>
      <p class="muted">Operational overview.</p>
    </div>

    <div class="metric-grid">
      <div class="panel compact">
        <div class="muted">Users</div>
        <div class="metric-value large">{{ data?.metrics.users ?? 0 }}</div>
      </div>
      <div class="panel compact">
        <div class="muted">Organizations</div>
        <div class="metric-value large">{{ data?.metrics.organizations ?? 0 }}</div>
      </div>
      <div class="panel compact">
        <div class="muted">Roles</div>
        <div class="metric-value large">{{ data?.metrics.roles ?? 0 }}</div>
      </div>
    </div>
  </section>
</template>
