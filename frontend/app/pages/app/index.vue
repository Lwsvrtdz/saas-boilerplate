<script setup lang="ts">
definePageMeta({
  layout: 'app',
  middleware: ['auth'],
})

const auth = useAuth()
</script>

<template>
  <section class="stack">
    <div>
      <h1>Dashboard</h1>
      <p class="muted">Welcome back, {{ auth.user.value?.name }}.</p>
    </div>

    <div class="metric-grid">
      <div class="panel compact">
        <div class="muted">Signed in as</div>
        <div class="metric-value">{{ auth.user.value?.name }}</div>
      </div>
      <div class="panel compact">
        <div class="muted">Current organization</div>
        <div class="metric-value">{{ auth.currentOrganization.value?.name ?? 'Not selected' }}</div>
      </div>
    </div>
  </section>
</template>
