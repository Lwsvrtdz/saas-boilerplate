<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: ['auth', 'admin'],
})

const { request } = useApi()

const { data } = await useAsyncData('admin-overview', () =>
  request<{ data: { metrics: { users: number; organizations: number; roles: number } } }>('/admin/overview')
)
</script>

<template>
  <section class="stack">
    <div class="panel" style="padding:1.5rem;">
      <div class="muted" style="text-transform:uppercase;letter-spacing:0.16em;font-size:0.8rem;">Admin</div>
      <h1 style="margin:0.35rem 0 0;">Operational overview</h1>
      <p class="muted">Keep this space generic for platform administration, audit tools, and internal controls.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;">
      <div class="panel" style="padding:1.25rem;">
        <div class="muted">Users</div>
        <div style="font-weight:700;font-size:1.5rem;">{{ data?.data.metrics.users ?? 0 }}</div>
      </div>
      <div class="panel" style="padding:1.25rem;">
        <div class="muted">Organizations</div>
        <div style="font-weight:700;font-size:1.5rem;">{{ data?.data.metrics.organizations ?? 0 }}</div>
      </div>
      <div class="panel" style="padding:1.25rem;">
        <div class="muted">Roles</div>
        <div style="font-weight:700;font-size:1.5rem;">{{ data?.data.metrics.roles ?? 0 }}</div>
      </div>
    </div>
  </section>
</template>
