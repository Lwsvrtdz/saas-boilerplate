<script setup lang="ts">
const auth = useAuth()

const links = [
  { label: 'Overview', to: '/app' },
  { label: 'Admin', to: '/admin' },
]
</script>

<template>
  <div class="app-shell">
    <aside class="sidebar">
      <div>
        <div class="brand">{{ auth.appName }}</div>
        <div class="muted">{{ auth.user.value?.email ?? 'Not authenticated' }}</div>
      </div>

      <OrganizationSwitcher />

      <nav class="nav-list">
        <NuxtLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="nav-link"
        >
          {{ link.label }}
        </NuxtLink>
      </nav>

      <div class="account-panel">
        <div style="font-weight:600;">{{ auth.user.value?.name ?? 'Guest' }}</div>
        <div class="muted">{{ auth.currentOrganization.value?.name ?? 'No organization' }}</div>
        <button class="button secondary full-width" type="button" @click="auth.logout">
          Sign out
        </button>
      </div>
    </aside>

    <main class="main-content">
      <slot />
    </main>
  </div>
</template>
